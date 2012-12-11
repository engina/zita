<?php
namespace Zita;

require_once('Core.php');
require_once('Request.php');
require_once('Response.php');
require_once('Controller.php');
require_once('Reflector.php');

class Dispatcher
{
	private static $appNs = '';
	private static $controllersDir = 'Controllers';
	
	public static function init($appNs = '', $controllersDir = 'Controllers')
	{
		self::$appNs = $appNs;
		self::$controllersDir = $controllersDir;
	}
	
	/**
	 * Discovers the available controllers to build API definition.
	 * @return \Zita\Response
	 */
	public static function discover()
	{
		$before = get_declared_classes();
		$files = scandir(self::$controllersDir);
		foreach($files as $file)
		{
			if(strtolower(substr($file, -4)) != '.php')
				continue;
			include self::$controllersDir.DIRECTORY_SEPARATOR.$file;
		}
		$after = get_declared_classes();
		$classes = array_diff($after, $before);
		
		$result = array();
		foreach($classes as $class)
		{
			$r = new \ReflectionClass($class);
			if(!$r->isSubclassOf('\Zita\Controller'))
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
	
	public static function dispatch()
	{
		$RESPONSE = array();
		try
		{
			$req = new Request();
			
			if($req->method == 'OPTIONS')
				goto respond;
			
			if(strtolower($req->server->QUERY_STRING) == 'discover')
			{
				$RESPONSE = self::discover();
				goto respond;
			}
			
			$c = $req->params->c;
			$m = $req->params->m;

			if($c === null || empty($c))
				$c = 'Default';
				
			if(!ctype_alnum($c))
				throw new Exception('Invalid controller name', 0);
				
			$c = self::$controllersDir.DIRECTORY_SEPARATOR.Core::normalize($c);
			
			if($m === null || empty($m))
				$m = 'index';
			$m = strtolower($m);
			
			// Load the controller - may throw exception.
			Core::load($c);

			$c = self::$appNs.'\\'.$c;
			
			// Class loaded fine, inspect it.
			
			$ctrl = new \ReflectionClass($c);
			
			if(!$ctrl->isSubclassOf('Zita\Controller'))
				throw new \Exception('Invalid controller implementation');

			if(!$ctrl->hasMethod($m))
				throw new \Exception('Method not found', 2);
				
			$method = $ctrl->getMethod($m);
			if(!$method->isPublic())
				throw new \Exception('Method not accessible', 3);

			
			$paramList = array();
			$params = $method->getParameters();
			foreach($params as $p => $param)
			{
				if($req->params->__get($param->name) == null && !$param->isOptional())
					throw new Exception('Missing parameters');
				array_push($paramList, $req->params->__get($param->name));
			}
			
			// annotations
			$a = Reflector::getMergedMethodAnnotation($c, $m);

			$ctrl = new $c($req);
			$RESPONSE = $method->invokeArgs($ctrl, $paramList);

			if(!($RESPONSE instanceof \Zita\Response))
				$RESPONSE = new Response($RESPONSE);
		}
		catch(\Exception $e)
		{
			// $RESPONSE = array('errno' => $e->getCode(), 'msg' => $e->getMessage());
			$RESPONSE = new Response(array('errno' => $e->getCode(), 'msg' => $e->getMessage()));
		}

		respond:
		header('HTTP/1.1 '.$RESPONSE->status);
		header('Expires: Mon, 20 Dec 1998 01:00:00 GMT');
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
		header('Cache-Control: no-cache, must-revalidate');
		header('Pragma: no-cache');
		header('Content-type: application/json');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Headers: origin, x-requested-with, content-type');
		foreach($RESPONSE->headers as $key => $value)
			header($key.': '.$value);
		
		if(!is_string($RESPONSE->body))
			$RESPONSE->body = var_export($RESPONSE->body);
		
		echo $RESPONSE->body;
	}
}

?>