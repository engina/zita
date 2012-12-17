<?php
namespace Zita;

class Reflector
{
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
			push:
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
		$ca = self::_getClassAnnotation($class, $inherit);
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
}