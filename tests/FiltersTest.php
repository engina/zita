<?php
require_once('Zita/Core.php');

use \Zita\Dispatcher;
use \Zita\Request;
use \Zita\Response;
use \Zita\Service;
use \Zita\Core;
use \Zita\IFilter;

class AFilter implements IFilter
{
    public function __construct($param)
    {

    }

    public function preProcess (Request $req, Response $resp, Dispatcher $dispatcher, Service $service, $method)
    {

    }

    public function postProcess (Request $req, Response $resp, Dispatcher $dispatcher, Service $service, $method)
    {
        $resp->body .= 'a';
    }
}

class BFilter implements IFilter
{
    public function __construct($param)
    {

    }

    public function preProcess (Request $req, Response $resp, Dispatcher $dispatcher, Service $service, $method)
    {

    }

    public function postProcess (Request $req, Response $resp, Dispatcher $dispatcher, Service $service, $method)
    {
        $resp->body .= 'b';
    }
}

class CFilter implements IFilter
{
    public function __construct($param)
    {

    }

    public function preProcess (Request $req, Response $resp, Dispatcher $dispatcher, Service $service, $method)
    {

    }

    public function postProcess (Request $req, Response $resp, Dispatcher $dispatcher, Service $service, $method)
    {
        $resp->body .= 'c';
    }
}

class ComplexFilterTestService extends Service
{
	/**
	 * @Filter A|A|B|C
	 */
	public function hello($name)
	{
		$this->response->body = "Hello $name";
	}	
}

class FiltersTest extends PHPUnit_Framework_TestCase
{
	public function testDispatcherFilter()
	{
		$req  = new Request();
		$d    = new Dispatcher();
		$name = 'John';
		$req->params->service = 'ComplexFilterTest';
		$req->params->method  = 'hello';
		$req->params->name    = $name;
		$resp = $d->dispatch($req);
		$this->assertEquals("Hello $name".'a'.'a'.'b'.'c', $resp->body);
	}
}