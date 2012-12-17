<?php
namespace Zita\Annotations;

use Zita\Annotations\FilterAnnotation;
use Zita\Request;
use Zita\Response;
use Zita\Service;
use Zita\Dispatcher;
use Zita\Core;


class InputFilterAnnotation extends FilterAnnotation
{
	public function postProcess(Request $req, Response $resp, Dispatcher $dispatcher, Service $service, $method)
	{
	}
}