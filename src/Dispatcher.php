<?php
namespace Zita;


require_once('Core.php');
require_once('Request.php');
require_once('Response.php');
require_once('Controller.php');
require_once('Reflector.php');
require_once('Event.php');

class Dispatcher
{
	private $appNs = '';
	private $controllersDir = 'Controllers';
	
	public $before;
	
	/**
	 * Draft. Events are being fired at the moment -- maybe never will.
	 * @code
	 * funciton json_encoder(Request $req, Response $resp)
	 * {
	 *     $resp->body = json_encode($resp->body);
	 *     $callback = $req->param('callback');
	 *     $resp->headers['Content-type'] = 'application/json';
	 *     if($callback != null)
	 *     {
	 *         $resp->body = $callback.'('.$resp->body.');';
	 *     $resp->headers['Content-type'] = 'application/script';
	 *     }
	 * }
	 * $dispatcher->after->add('json_encoder');
	 */
	public $after;
	
	/**
	 * 
	 * @param $appNs namespace of your application, will be prepended to Controller class names. Normally you'd just use __NAMESPACE__
	 * @param $controllersDir directory of controllers
	 */
	public function __construct($appNs = '', $controllersDir = 'Controllers')
	{
		$this->appNs = $appNs;
		$this->controllersDir = $controllersDir;
		$this->before = new Event();
		$this->after  = new Event();
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
	
	public function dispatch(Request $req = null)
	{
		$RESPONSE = array();
		try
		{
			if($req === null)
				$req = new Request();
			
			if($req->method == 'OPTIONS')
				goto respond;
			
			if(strtolower($req->server->QUERY_STRING) == 'discover')
			{
				$RESPONSE = $this->discover();
				goto respond;
			}
			
			$c = $req->params->c;
			$m = $req->params->m;

			if($c === null || empty($c))
				$c = 'Default';
				
			if(!ctype_alnum($c))
				throw new Exception('Invalid controller name', 0);
			
			if($m === null || empty($m))
				$m = 'index';
			$m = strtolower($m);
			
			// Class might be loaded in case the user is defined it in file -- for testing purposes maybe.
			// So, try to load the file if the class does not exist.
			if(!class_exists($this->appNs.'\\'.$c, false))
			{
				$c = $this->controllersDir.DIRECTORY_SEPARATOR.Core::normalize($c);
				Core::load($c);
			}
			
			$c = $this->appNs.'\\'.$c;
			
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

			// Allow simple methods to just return text
			if(!($RESPONSE instanceof \Zita\Response))
				$RESPONSE = new Response($RESPONSE);
			
			if(isset($a['Encode']))
			{
				$encoder = ZITA_ROOT.DS.'Encoders'.DS.Core::normalize($a['Encode']);
				error_log('Loading encoder '.$encoder);
				Core::load($encoder);
				$encoder = 'Zita\Encoders\\'.Core::normalize($a['Encode']);
				$encoder = new $encoder($req, $RESPONSE);
				$encoder->encode();
			}
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
		return $RESPONSE;
	}
}

?>