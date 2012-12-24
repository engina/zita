<?php
namespace Zita;

class Reflector
{
    /**
     * Annotations which are supposed to be ignored.
     */
    public static $IGNORED_ANNOTATIONS = array('return', 'param', 'throws');

    protected static function parseAnnotation($doccomment)
    {
        $result = array();
        $lines = explode("\n", $doccomment);
        foreach($lines as $line)
        {
            $key = null;
            $value = null;
            $line = trim($line);
            if(substr($line, 0, 1) == '*')
                $line = substr($line, 1);

            $line = trim($line);
            if(substr($line, 0, 1) != '@')
                continue;
            $valueStart = strpos($line, ' ');
            if($valueStart === false)
            {
                // No value annotation
                $key = substr($line, 1);
                $value = null;
                goto push;
            }

            $key = trim(substr($line, 1, $valueStart));
            $value = trim(substr($line, $valueStart));
            if(strpos($value, ';') !== false)
            {
                // Multi value annotation
                $values = explode(';', $value);
                $value = array();
                foreach($values as $v)
                {
                    $v = trim($v);
                    if(strpos($v, '=') !== false)
                    {
                        // Key value pair
                        list($value_key, $value_value) = explode('=', $v);
                        $value[trim($value_key)] = trim($value_value);
                    }
                    else
                    {
                        // Just a key
                        array_push($value, $v);
                    }
                }
            }
            else
            {
                // Single value annotation
                if($value === 'true')
                    $value = true;
                if($value === 'false')
                    $value = false;
                if($value === 'null')
                    $value = null;
            }

            push:
            if(in_array($key, self::$IGNORED_ANNOTATIONS))
                continue;
            $result[$key] = $value;
        }
        return $result;
    }

    private static function _getClassAnnotation(\ReflectionClass $class, $inherit)
    {
        $annotations = self::parseAnnotation($class->getDocComment());
        if(!$inherit)
            return $annotations;

        while(($class = $class->getParentClass()) !== false)
        {
            $annotations = array_merge(self::parseAnnotation($class->getDocComment()), $annotations);
        }
        return $annotations;
    }

    private static function _getMethodAnnotation(\ReflectionClass $class, $method)
    {
        $m = $class->getMethod($method);
        return self::parseAnnotation($m->getDocComment());
    }

	/**
	 * Parses annotations of a class.
	 *
     * Annotation format is
     *
     * @Key arbitrary text parameters
     * @NoValueKey
     *
     * This will result in
     * array(
     *   'Key' => 'arbitrary text parameters',
     *   'NoValueKey' => null
     * )
     *
     * So KeyAnnotation class will be constructed with parameter "arbitrary text parameter" and NoValueKeyAnnotation
     * class will be constructed with null parameter.
	 * 
	 * @param $class name of the class
	 * @return an array of parsed annotations
	 */
	public static function getClassAnnotation($class, $inherit = true)
	{
		return self::_getClassAnnotation(new \ReflectionClass($class), $inherit);
	}

	/**
	 * Extracts annotations from class, then from method. Annotations of the method overrides the class annotations.
     *
     * Inheriting parent class method annotations logic explained below.
     *
     * ClassChild derives from ClassParent.
     *
     * To get annotations of ClassChişd::foo which is overriding ClassParent::foo. Following algorithm is applied.
     *
     * 1) ClassParent annotations are parsed
     * 2) ClassParent::foo annotations are parsed
     * 3) ClassParent::foo annotations override ClassParent annotations
     * 4) Same logic is applied for ClassChild::foo
     * 5) The resulting annotations of ClassChild::foo overrides the resulting annotations of ClassParent::foo
     *
	 * @param string $class  name of the class
	 * @param string $method name of the method
     * @param bool   $inherit inherits annotations from parent classes and methods.
	 * @return an array of parsed annotations as array
	 * @see    getClassAnnotation($class)
	 */
	public static function getMethodAnnotation($class, $method, $inherit = true)
	{
        $class = new \ReflectionClass($class);
		$ca = self::_getClassAnnotation($class, false);
		$ma = self::_getMethodAnnotation($class, $method);
		$annotations = array_merge($ca, $ma);
        if(!$inherit)
            return $annotations;
        while(($class = $class->getParentClass()) !== false)
        {
            $parentAnnotations = self::_getClassAnnotation($class, $inherit);
            if($class->hasMethod($method))
            {
                $parentAnnotations = array_merge($parentAnnotations, self::_getMethodAnnotation($class, $method));
            }
            $annotations = array_merge($parentAnnotations, $annotations);
        }
        return $annotations;
	}

    /**
     * Checks the provided method's parameter list against $params and prepares an array to be passed to
     * ReflectionMethod::invokeArgs().
     *
     * If $params lack some non-optional parameters of the given method, Zita\ReflectionException will be thrown.
     *
     * <code>
     * class Foo
     * {
     *   public function bar($name, $surname);
     * }
     *
     * $params = array('x' => 'y', 'name' => 'John', 'surname' => 'Doe', 'a' => 'b');
     * var_dump(Reflector::invokeArgs(new ReflectionMethod('Foo', 'bar'), $params));
     * </code>
     * Will print array('John', 'Doe'). As those are the required parameters for the method to be run via
     * ReflectionMethod::invokeArgs
     *
     * @param \ReflectionMethod $method
     * @param array $params
     * @return array
     * @throws ReflectionException
     */
    public static function invokeArgs(\ReflectionMethod $method, $params, $ignoreSuperfluous = false)
    {
        if(!is_array($params))
            $params = array($params);
        $paramList = array();

        $methodParams = $method->getParameters();

        // If the method requires a single parameter and annotation parameter is a single value (@foo bar) just pass
        // bar to the method as parameter and do not require (@foo param=bar) notation.
        if(count($methodParams) == 1 && count($params) == 1)
            return $params;

        foreach($methodParams as $p => $param)
        {
            if(!isset($params[$param->name]))
            {
                // This parameter is not in the provided $params, if it is not an optional parameter this is a fatal error
                if(!$param->isOptional())
                    throw new ReflectionException($method->class.'::'.$method->name.'() requires parameter: '.$param->name);
                // OK, we don't have it and it is optional, so we can just skip this one.
                continue;
            }
            array_push($paramList, $params[$param->name]);
            unset($params[$param->name]);
        }
        if(!$ignoreSuperfluous && count($params) > 0)
            throw new ReflectionException($method->class.'::'.$method->name.'() has some superfluous parameters: '.implode(', ', array_keys($params)));
        return $paramList;
    }

    public static function discover()
    {
        foreach(Core::getIncludePaths() as $path)
        {
            foreach(glob(Core::path($path, '*Service.php'))  as $service)
            {
                require_once($service);
            }
        }
        $classes = get_declared_classes();

        $result = array();
        foreach($classes as $class)
        {
            if(substr($class, -7) != 'Service') continue;

            $r = new \ReflectionClass($class);
            if(!$r->isSubclassOf('\Zita\Service'))
                continue;
            if(!$r->isInstantiable())
                continue;
            $m = array();
            $methods = $r->getMethods(\ReflectionMethod::IS_PUBLIC);
            foreach($methods as $method)
            {
                if($method->isConstructor()) continue;

                $parameters = array();
                foreach($method->getParameters() as $parameter)
                {
                    $param_info = array();
                    $param_info['optional'] = false;
                    if($parameter->isDefaultValueAvailable())
                    {
                        $param_info['default'] = $parameter->getDefaultValue();
                        $param_info['optional'] = true;
                    }
                    $type = $parameter->getClass();
                    if($type == null)
                        $type = 'String';
                    $param_info['type'] = $type;
                    $parameters[$parameter->getName()] = $param_info;
                }
                $m[$method->getName()] = array('parameters' => $parameters);
            }

            if(count($m) == 0) continue;
            $result[$r->getShortName()] = $m;
        }
        return $result;
    }
}