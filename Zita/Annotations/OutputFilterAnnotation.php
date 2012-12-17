<?php
namespace Zita\Annotations;

use Zita\IAnnotation;
use Zita\Request;
use Zita\Response;
use Zita\Service;
use Zita\Dispatcher;
use Zita\Core;


class OutputFilterAnnotation extends FilterAnnotation
{
	public function preProcess(Request $req, Response $resp, Dispatcher $dispatcher, Service $service, $method)
	{
	}
}