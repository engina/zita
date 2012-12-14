<?php
namespace Zita\Annotations;

use Zita\IAnnotation;
use Zita\Request;
use Zita\Response;
use Zita\Service;

class SecureAnnotation implements IAnnotation
{
	public function __construct($params)
	{
		
	}

	public function preProcess (Request $req, Response $resp, Service $service = null, $method = null)
	{
		throw new \Exception("Authentication is required.", 101);
	}

	public function postProcess (Request $req, Response $resp, Service $service = null, $method = null)
	{
		
	}
}