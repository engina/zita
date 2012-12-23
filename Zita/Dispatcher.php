<?php
namespace Zita;

require_once('DispatcherException.php');

const DISPATCHER_ERROR_INVALID_SERVICE_NAME = 0;
const DISPATCHER_ERROR_SERVICE_NOT_FOUND    = 1;
const DISPATCHER_ERROR_SERVICE_IMPL         = 2;
const DISPATCHER_ERROR_METHOD_NOT_FOUND     = 3;
const DISPATCHER_ERROR_METHOD_PARAM         = 4;
const DISPATCHER_ERROR_METHOD_ACCESS        = 5;
const DISPATCHER_ERROR_ANNOTATION_IMPL      = 6;
const DISPATCHER_ERROR_ANNOTATION_EXCEPTION = 7;
const DISPATCHER_ERROR_UNCAUGHT_EXCEPTION   = 8;

/**
 * Dispatcher class is responsible for orchestrating the workflow.
 * 
 * It creates a Request object. Calls the _before_ handlers before calling the
 * controllers then calls the _after_ handlers before flushing the Response.
 */
class Dispatcher
{
    private $sessionProvider;

    public function __construct()
    {
        $this->setSessionProvider(new FileSessionProvider(Core::path(APP_ROOT, 'tmp', 'sessions')));
    }

    public function setSessionProvider(ISessionProvider $provider)
    {
        $this->sessionProvider = $provider;
    }

    public function getSessionProvider()
    {
        return $this->sessionProvider;
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

            $service .= 'Service';

			$classPath = '';
			try
			{
				$classPath = Core::load(ucfirst($service));
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

            $service = new $classPath($req, $resp, $this);

			// annotations
			$annotations = Reflector::getMethodAnnotation($classPath, $m);
			$annotationInstances = array();

            foreach($annotations as $annotation => $params)
            {
                $annotation .= 'Annotation';
                $classPath   = Core::load($annotation);
                $class       = new \ReflectionClass($classPath);
                if(!$class->isSubclassOf('\Zita\IAnnotation'))
                    throw new DispatcherException("Annotation class does not implement IAnnotation interface.", DISPATCHER_ERROR_ANNOTATION_IMPL);
                $args        = Reflector::invokeArgs($class->getConstructor(), $params);
                $annotation  = $class->newInstanceArgs($args);
                $annotationInstances[] = $annotation;
            }

			try
            {
                foreach($annotationInstances as $annotation)
                {
                    $annotation->preProcess($req, $resp, $this, $service, $m);
                }
                if($req->handled !== true)
                {
                    $args = Reflector::invokeArgs($method, $req->params->toArray(), true);
                    $method->invokeArgs($service, $args);
                }
            }
			catch(\Exception $e)
            {
                // Pre annotation procesing or actual service threw an exception.
                // We are supposed to handle these exceptions gracefully as they are likely to be thrown on purpose
                // to report an issue (i.e. Authorization problem). Post processing from now on can re-format this error message (i.e. to XML).
                $resp->body = array('status' => 'FAIL', 'type' => get_class($e), 'errno' => $e->getCode(), 'msg' => $e->getMessage());
            }

            foreach($annotationInstances as $annotation)
            {
                $annotation->postProcess($req, $resp, $this, $service, $m);
            }
		}
		catch(\Exception $e)
		{
            // This is a critical error. An exception is raised in post processing of annotation. From now on, the result
            // is not reliable.
            //! FIXME: Do not display these errors in production.
            $resp->body = array('status' => 'FAIL', 'type' => get_class($e), 'errno' => $e->getCode(), 'msg' => $e->getMessage());
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

		// If a hand crafted Request is given, we don't flush the output, instead we just return.
		if(!$flush) return $resp;

        // Everything came to this! We are about to flush the data. If it is not printable, make it so.
		if(!is_string($resp->body))
			$resp->body = json_encode($resp->body);

		header('HTTP/1.1 '.$resp->status);
		foreach($resp->headers as $key => $value)
			header($key.': '.$value);
		echo $resp->body;
	}
}

?>