<?php
namespace Zita\Annotations;

require_once('IAnnotation.php');

use Zita\IAnnotation;
use Zita\Request;
use Zita\Response;
use Zita\Controller;

class SecureAnnotation implements IAnnotation
{
	public function __construct($params)
	{
		
	}

	public function preProcess (Request $req, Response $resp, Controller $controller = null, $method = null)
	{
		throw new \Exception("Authentication is required.", 101);
	}

	public function postProcess (Request $req, Response $resp, Controller $controller = null, $method = null)
	{
		
	}
}