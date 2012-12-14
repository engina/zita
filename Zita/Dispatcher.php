<?php
namespace Zita;

const DISPATCHER_ERROR_INVALID_SERVICE_NAME = 0;
const DISPATCHER_ERROR_SERVICE_NOT_FOUND    = 1;
const DISPATCHER_ERROR_SERVICE_IMPL         = 2;
const DISPATCHER_ERROR_METHOD_NOT_FOUND     = 3;
const DISPATCHER_ERROR_METHOD_PARAM         = 4;
const DISPATCHER_ERROR_METHOD_ACCESS        = 5;
const DISPATCHER_ERROR_ANNOTATION_IMPL      = 6;
 
/**
 * Dispatcher class is responsible for orchestrating the workflow.
 * 
 * It creates a Request object. Calls the _before_ handlers before calling the
 * controllers then calls the _after_ handlers before flushing the Response.
 */
class Dispatcher
{
	public  $pluginContainer = null;
	
	public function __construct()
	{
		$this->pluginContainer  = new PluginContainer();
	}
	
	/**
	 * Discovers the available controllers to build API definition.
	 * @return \Zita\Response
	 */
	public function discover()
	{
		$before = get_declared_classes();
		$files = scandir($this->controllersDir);
		foreach($files as $file)
		{
			if(strtolower(substr($file, -4)) != '.php')
				continue;
			include $this->controllersDir.DIRECTORY_SEPARATOR.$file;
		}
		$after = get_declared_classes();
		$classes = array_diff($after, $before);
		
		$result = array();
		foreach($classes as $class)
		{
			$r = new \ReflectionClass($class);
			if(!$r->isSubclassOf('\Zita\Service'))
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
		return new Response($result);
	}
	
	/**
	 * 
	 * @param Request $req You can craft your own Request and feed the dispatcher with it. Handy for testing your API.
	 * @throws \Zita\Exception
	 * @return \Zita\Response
	 */
	public function dispatch(Request $req = null)
	{
		$resp = new Response();
		try
		{
			$flush = false;
			if($req === null)
			{
				$req = new Request();
				$flush = true;
			}
			
			if($this->pluginContainer->preProcess($req, $resp) === true)
				goto respond;
			
			if($req->method == 'OPTIONS')
				goto respond;
			
			if(strtolower($req->server->QUERY_STRING) == 'discover')
			{
				$RESPONSE = $this->discover();
				goto respond;
			}
			
			$service = $req->params->service;
			$m       = $req->params->method;

			if($service === null || empty($service))
				$service = 'Default';
				
			if(!ctype_alnum($service))
				throw new DispatcherException('Invalid service name', DISPATCHER_ERROR_INVALID_SERVICE_NAME);
			
			if($m === null || empty($m))
				$m = 'index';
			
			$m = strtolower($m);
			
			$classPath = '';
			try
			{
				$classPath = Core::load(ucfirst($service), Core::getServicePaths());
			}
			catch(ClassNotFoundException $e)
			{
				throw new DispatcherException("Could not find service '$service'", DISPATCHER_ERROR_SERVICE_NOT_FOUND);
			}
			
			$service = $classPath;
			
			// Class loaded fine, inspect it.
			$serviceReflection = new \ReflectionClass($classPath);
			
			if(!$serviceReflection->isSubclassOf('Zita\Service'))
				throw new DispatcherException('Invalid service implementation.', DISPATCHER_ERROR_SERVICE_IMPL);

			if(!$serviceReflection->hasMethod($m))
				throw new DispatcherException('Method not found.', DISPATCHER_ERROR_METHOD_NOT_FOUND);
				
			$method = $serviceReflection->getMethod($m);
			if(!$method->isPublic())
				throw new DispatcherException('Method not accessible.', DISPATCHER_ERROR_METHOD_ACCESS);

			// annotations
			$annotations = Reflector::getMergedMethodAnnotation($classPath, $m);
			
			$service = new $classPath($req, $resp);
			
			foreach($annotations as $annotation => $params)
			{
				$annotation .= 'Annotation';
				$classPath = Core::load($annotation);
				$annotation = new $classPath($params);
				if(!($annotation instanceof IAnnotation))
					throw new DispatcherException("Annotation class does not implement IAnnotation interface.", DISPATCHER_ERROR_ANNOTATION_IMPL);
				$annotation->preProcess($req, $resp, $service, $m);
			}
			
			$paramList = array();
			$params = $method->getParameters();
			foreach($params as $p => $param)
			{
				if($req->params->__get($param->name) == null && !$param->isOptional())
					throw new DispatcherException('Missing parameters.', DISPATCHER_ERROR_METHOD_PARAM);
				array_push($paramList, $req->params->__get($param->name));
			}
			
			$method->invokeArgs($service, $paramList);

			foreach($annotations as $annotation => $params)
			{
				$annotation .= 'Annotation';
				$classPath = Core::load($annotation);
				$annotation = new $classPath($params);
				$annotation->postProcess($req, $resp, $service, $m);
			}
			
			$this->pluginContainer->postProcess($req, $resp);
		}
		catch(\Exception $e)
		{
			// $RESPONSE = array('errno' => $e->getCode(), 'msg' => $e->getMessage());
			$resp = new Response(array('status' => 'FAIL', 'type' => get_class($e), 'errno' => $e->getCode(), 'msg' => $e->getMessage()));
		}

		respond:
		
		// Default headers
		$headers = array(
			'Expires'       => 'Mon, 20 Dec 1998 01:00:00 GMT',
			'Last-Modified' => gmdate('D, d M Y H:i:s').' GMT',
			'Cache-Control' => 'no-cache, must-revalidate',
		    'Pragma'        => 'no-cache',
		    'Content-type'  => 'application/json',
		    'Access-Control-Allow-Origin' => '*',
		    'Access-Control-Allow-Headers' => 'origin, x-requested-with, content-type'
		);
		
		// Let controller defined headers override defaults
		$resp->headers = array_merge($headers, $resp->headers);
		
		if(!is_string($resp->body))
			$resp->body = var_export($resp->body, true);

		// If a hand crafted Request is given, we don't flush the output, instead we just return.
		if(!$flush) return $resp;

		header('HTTP/1.1 '.$resp->status);
		foreach($resp->headers as $key => $value)
			header($key.': '.$value);
		echo $resp->body;
	}
}

?>