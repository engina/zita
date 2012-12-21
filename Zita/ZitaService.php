<?php
namespace Zita;
/**
 * User: Engin
 * Date: 17.12.2012
 * Time: 17:45
 */
class ZitaService extends  Service
{
    /**
     * Discovers the available controllers to build API definition.
     */
    public function discover()
    {
        $before = get_declared_classes();
        foreach(Core::getIncludePaths() as $path)
        {
            foreach(glob(Core::path($path, '/*Service.php'))  as $service)
            {
                require_once($service);
            }
        }
        $after = get_declared_classes();
        $classes = array_diff($after, $before);

        $result = array();
        foreach($classes as $class)
        {
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
        $this->response->body = $result;
    }
}
