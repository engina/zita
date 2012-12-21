<?php
require_once('Zita/Core.php');

use Zita\Request;
use Zita\Dispatcher;
use Zita\Service;
use Zita\Response;
use Zita\DISPATCHER_ERROR_CONTROLLER_NOT_FOUND;
use Zita\DISPATCHER_EXCEPTION_BASE;

class DispatcherTestService extends Service
{
	public function hello($name)
	{
        $this->response->body = "Hello $name";
	}

	private function secret()
	{
	}
}

class DispatcherTestNoImplService
{
	public function hello($name)
	{
		$this->response->body = "Hello $name";
	}
}

class DispatcherTest extends PHPUnit_Framework_TestCase
{
	public function testInvalidServiceName()
	{
		$req = new Request();
		$req->params->service = 'Invalid..Service';
		$req->params->method = 'hello';
		$d = new Dispatcher();
		$resp = $d->dispatch($req);
		$expected = array('status' => 'FAIL', 'type' => 'Zita\\DispatcherException', 'errno' => \Zita\DISPATCHER_EXCEPTION_BASE + \Zita\DISPATCHER_ERROR_INVALID_SERVICE_NAME, 'msg' => 'Invalid service name');
		$this->assertEquals($expected, $resp->body);
	}
	
	public function testInvalidService()
	{
		$req = new Request();
		$req->params->service = 'Invalid';
		$req->params->method = 'hello';
		$d = new Dispatcher();
        $resp = $d->dispatch($req);
		$expected = array('status' => 'FAIL', 'type' => 'Zita\\DispatcherException', 'errno' => \Zita\DISPATCHER_EXCEPTION_BASE + \Zita\DISPATCHER_ERROR_SERVICE_NOT_FOUND, 'msg' => "Could not find service 'InvalidService'");
        $this->assertEquals($expected, $resp->body);
	}
	
	public function testWrongImplementation()
	{
		$req = new Request();
		$req->params->service = 'DispatcherTestNoImpl';
		$req->params->methodethod = 'hello';
		$d = new Dispatcher();
        $resp = $d->dispatch($req);
		$expected = array('status' => 'FAIL', 'type' => 'Zita\\DispatcherException', 'errno' => \Zita\DISPATCHER_EXCEPTION_BASE + \Zita\DISPATCHER_ERROR_SERVICE_IMPL, 'msg' => 'Invalid service implementation.');
        $this->assertEquals($expected, $resp->body);
	}
		
	public function testInvalidMethod()
	{
		$req = new Request();
		$req->params->service = 'DispatcherTest';
		$req->params->method = 'hola';
		$d = new Dispatcher();
        $resp = $d->dispatch($req);
		$expected = array('status' => 'FAIL', 'type' => 'Zita\\DispatcherException', 'errno' => \Zita\DISPATCHER_EXCEPTION_BASE + \Zita\DISPATCHER_ERROR_METHOD_NOT_FOUND, 'msg' => 'Method not found.');
        $this->assertEquals($expected, $resp->body);
	}
	
	public function testMethodAccessViolation()
	{
		$req = new Request();
		$req->params->service = 'DispatcherTest';
		$req->params->method = 'secret';
		$d = new Dispatcher();
        $resp = $d->dispatch($req);
		$expected = array('status' => 'FAIL', 'type' => 'Zita\\DispatcherException', 'errno' => \Zita\DISPATCHER_EXCEPTION_BASE + \Zita\DISPATCHER_ERROR_METHOD_ACCESS, 'msg' => 'Method not accessible.');
        $this->assertEquals($expected, $resp->body);
	}
	
	public function testMissingParam()
	{
		$req = new Request();
		$req->params->service = 'DispatcherTest';
		$req->params->method = 'hello';
		$d = new Dispatcher();
        $resp = $d->dispatch($req);
		$expected = array('status' => 'FAIL', 'type' => 'Zita\\DispatcherException', 'errno' => \Zita\DISPATCHER_EXCEPTION_BASE + \Zita\DISPATCHER_ERROR_METHOD_PARAM, 'msg' => 'Missing parameter: name');
        $this->assertEquals(json_encode($expected), $resp->body);
	}

	public function testNormal()
	{
		$req = new Request();
		$name = 'John';
		$req->params->service = 'DispatcherTest';
		$req->params->method = 'hello';
		$req->params->name = $name;
		$d = new Dispatcher();
		$response = $d->dispatch($req);
		$this->assertEquals(json_encode("Hello $name"), $response->body);
	}
}