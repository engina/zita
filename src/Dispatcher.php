<?php
namespace Zita;

require_once('Core.php');
require_once('Request.php');
require_once('Response.php');
require_once('Controller.php');
require_once('Reflector.php');
require_once('IAnnotation.php');
require_once('Filters.php');

/**
 * Dispatcher class is responsible for orchestrating the workflow.
 * 
 * It creates a Request object. Calls the _before_ handlers before calling the
 * controllers then calls the _after_ handlers before flushing the Response.
 */
class Dispatcher
{
	private $appNs = '';
	private $controllersDir = 'Controllers';


	/**
	 * Before event handlers receive parameter Request.
	 * If an event handler returns false, subsequent handlers won't be called.
	 * <code>
	 * funciton json_decoder(Request $req, Response $resp)
	 * {
	 *     if($resp->headers['Content-type'] != 'application/json') return;
	 *     $resp->params = json_decode($resp->body);
	 * }
	 * $dispatcher->before->add('json_decoder');
	 * </code>
	 */
	public $inputFilters;
	
	/**
	 * After event handlers receive parameters Request and Response.
	 * If an event handler returns false, subsequent handlers won't be called.
	 * <code>
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
	 * </code>
	 * $dispatcher->after->add('json_encoder');
	 */
	public $outputFilters;
	
	/**
	 * Initiliazes and configures dispatchers.
	 * 
	 * __api.php__
	 * <code>
	 * namespace MyApp;
	 * 
	 * require_once('zita/src/Dispatcher.php');
	 * 
	 * $d = new Zita\Dispatcher(__NAMESPACE__);
	 * $d->dispatch();
	 * </code>
	 * 
	 * __Controllers\Test.php__
	 * <code>
	 * namespace MyApp\Controllers;
	 * 
	 * class Test extends Zita\Controllers
	 * {
	 *     public function hello($name)
	 *     {
	 *         return new Response("Hello $name");
	 *     }
	 * }
	 * </code>
	 * @param $appNs namespace of your application, will be prepended to Controller class names. Normally you'd just use \_\_NAMESPACE\_\_
	 * @param $controllersDir directory of controllers
	 */
	public function __construct($appNs = '', $controllersDir = 'Controllers')
	{
		$this->appNs = $appNs;
		$this->controllersDir = $controllersDir;
		Core::$INCLUDES[] = APP_ROOT.DS.$controllersDir;
		$this->inputFilters  = new Filters();
		$this->outputFilters = new Filters();
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
	
	/**
	 * 
	 * @param Request $req You can craft your own Request and feed the dispatcher with it. Handy for testing your API.
	 * @throws \Zita\Exception
	 * @return \Zita\Response
	 */
	public function dispatch(Request $req = null)
	{
		$RESPONSE = array();
		try
		{
			if($req === null)
				$req = new Request();
			
			$this->inputFilters->process($req, new Response());
			
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
			
			$classPath = Core::load(Core::normalize($c));
			
			if($classPath === false)
				throw new Exception("Controller '$c' not found");
			
			$c = $classPath;
			
			// Class loaded fine, inspect it.
			$ctrl = new \ReflectionClass($c);
			
			if(!$ctrl->isSubclassOf('Zita\Controller'))
				throw new Exception('Invalid controller implementation');

			if(!$ctrl->hasMethod($m))
				throw new Exception('Method not found', 2);
				
			$method = $ctrl->getMethod($m);
			if(!$method->isPublic())
				throw new Exception('Method not accessible', 3);
			
			// annotations
			$annotations = Reflector::getMergedMethodAnnotation($c, $m);
			
			$ctrl = new $c($req);
			
			foreach($annotations as $annotation => $params)
			{
				$annotation .= 'Annotation';
				$annotationClass = Core::load($annotation);
				if($annotationClass === false)
					throw new Exception("Unknown annotation '$annotation'");
				$annotation = new $annotationClass($params);
				if(!($annotation instanceof IAnnotation))
					throw new Exception("Annotation class does not implement IAnnotation interface");
				$annotation->preProcess($req, new Response(), $ctrl, $m);
			}
			
			$paramList = array();
			$params = $method->getParameters();
			foreach($params as $p => $param)
			{
				if($req->params->__get($param->name) == null && !$param->isOptional())
					throw new Exception('Missing parameters');
				array_push($paramList, $req->params->__get($param->name));
			}
			
			$RESPONSE = $method->invokeArgs($ctrl, $paramList);
			
			foreach($annotations as $annotation => $params)
			{
				$annotation .= 'Annotation';
				$annotationClass = Core::load($annotation);
				$annotation = new $annotationClass($params);
				$annotation->postProcess($req, $RESPONSE, $ctrl, $m);
			}
			
			$this->outputFilters->process($req, $RESPONSE);
			
			// Allow simple methods to just return text
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
		$headers = array_merge($headers, $RESPONSE->headers);
		
		foreach($headers as $key => $value)
			header($key.': '.$value);
		
		if(!is_string($RESPONSE->body))
			$RESPONSE->body = var_export($RESPONSE->body);
		
		echo $RESPONSE->body;
		return $RESPONSE;
	}
}

?>