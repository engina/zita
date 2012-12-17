<?php
namespace Zita\Annotations;

use Zita\IAnnotation;
use Zita\Request;
use Zita\Response;
use Zita\Service;
use Zita\Dispatcher;

class SecureAnnotation implements IAnnotation
{
	public function __construct($params)
	{
		
	}

	public function preProcess (Request $req, Response $resp, Dispatcher $dispatcher, Service $service, $method)
	{
        if($req->params->access != null)
        {
            $provider = $dispatcher->getSessionProvider();
            $session  = $provider->load($req->params->access);
            $req->user = $session->user;
        }
	}

	public function postProcess (Request $req, Response $resp, Dispatcher $dispatcher, Service $service, $method)
	{
		
	}
}