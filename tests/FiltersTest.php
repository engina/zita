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
        $req->params->name .= 'A';
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
        $req->params->name .= 'B';
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
        $req->params->name .= 'C';
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

    /**
     * @InputFilter A|B|C|A
     */
    public function hola($name)
    {
        $this->response->body = "Hello $name";
    }


    /**
     * @OutputFilter A|B|C|A
     */
    public function hallo($name)
    {
        $this->response->body = "Hello $name";
    }

    /**
     * Default @OutputFilter AutoFormat is active.
     */
    public function complexOutput($name)
    {
        $this->response->body = array('name' => $name, 'msg' => 'Hello');
    }

    /**
     * Disable default output format so raw output gets out.
     *
     * In fact lets put up some very stupid stuff and see if it can handle it.
     * @OutputFilter ||||
     */
    public function rawOutput($name)
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
        $req->params->type    = 'raw';
		$resp = $d->dispatch($req);
		$this->assertEquals("Hello $name".'AABCaabc', $resp->body);
	}

    public function testDispatcherInputFilter()
    {
        $req  = new Request();
        $d    = new Dispatcher();
        $name = 'John';
        $req->params->service = 'ComplexFilterTest';
        $req->params->method  = 'hola';
        $req->params->name    = $name;
        $req->params->type    = 'raw';
        $resp = $d->dispatch($req);
        $this->assertEquals("Hello $name".'ABCA', $resp->body);
    }

    public function testDispatcherOutputFilter()
    {
        $req  = new Request();
        $d    = new Dispatcher();
        $name = 'John';
        $req->params->service = 'ComplexFilterTest';
        $req->params->method  = 'hallo';
        $req->params->name    = $name;
        $resp = $d->dispatch($req);
        $this->assertEquals("Hello $name".'abca', $resp->body);
    }

    public function testDispatcherAutoFormatFilter()
    {
        $req  = new Request();
        $d    = new Dispatcher();
        $name = 'John';
        $req->params->service = 'ComplexFilterTest';
        $req->params->method  = 'complexOutput';
        $req->params->name    = $name;
        $req->params->type    = 'raw';
        $resp = $d->dispatch($req);
        $expected = array('name' => $name, 'msg' => 'Hello');
        $this->assertEquals($expected, $resp->body);;

        $req->params->type = 'json';
        $resp = $d->dispatch($req);
        $this->assertEquals(json_encode($expected), $resp->body);

        $req->params->callback = 'myfunc';
        $resp = $d->dispatch($req);
        $this->assertEquals('myfunc('.json_encode($expected).');', $resp->body);

        $req->params->method = 'rawOutput';
        $resp = $d->dispatch($req);
        $this->assertEquals("Hello $name", $resp->body);
    }
}