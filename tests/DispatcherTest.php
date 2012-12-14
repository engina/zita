<?php

require_once('api/0.1/zita/Zita/Core.php');

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
		return new Response("Hello $name");
	}
	
	private function secret()
	{
	}
}

class DispatcherTestServiceNoImpl
{
	public function hello($name)
	{
		return new Response("Hello $name");
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
		$d->dispatch($req);
		$expected = array('status' => 'FAIL', 'type' => 'Zita\\DispatcherException', 'errno' => \Zita\DISPATCHER_EXCEPTION_BASE + \Zita\DISPATCHER_ERROR_INVALID_SERVICE_NAME, 'msg' => 'Invalid service name');
		$this->expectOutputString(var_export($expected, true));
	}
	
	public function testInvalidService()
	{
		$req = new Request();
		$req->params->service = 'InvalidService';
		$req->params->method = 'hello';
		$d = new Dispatcher();
		$d->dispatch($req);
		$expected = array('status' => 'FAIL', 'type' => 'Zita\\DispatcherException', 'errno' => \Zita\DISPATCHER_EXCEPTION_BASE + \Zita\DISPATCHER_ERROR_SERVICE_NOT_FOUND, 'msg' => "Could not find service 'InvalidService'");
		$this->expectOutputString(var_export($expected, true));
	}
	
	public function testWrongImplementation()
	{
		$req = new Request();
		$req->params->service = 'DispatcherTestServiceNoImpl';
		$req->params->methodethod = 'hello';
		$d = new Dispatcher();
		$d->dispatch($req);
		$expected = array('status' => 'FAIL', 'type' => 'Zita\\DispatcherException', 'errno' => \Zita\DISPATCHER_EXCEPTION_BASE + \Zita\DISPATCHER_ERROR_SERVICE_IMPL, 'msg' => 'Invalid service implementation.');
		$this->expectOutputString(var_export($expected, true));
	}
		
	public function testInvalidMethod()
	{
		$req = new Request();
		$req->params->service = 'DispatcherTestService';
		$req->params->method = 'hola';
		$d = new Dispatcher();
		$d->dispatch($req);
		$expected = array('status' => 'FAIL', 'type' => 'Zita\\DispatcherException', 'errno' => \Zita\DISPATCHER_EXCEPTION_BASE + \Zita\DISPATCHER_ERROR_METHOD_NOT_FOUND, 'msg' => 'Method not found.');		
		$this->expectOutputString(var_export($expected, true));
	}
	
	public function testMethodAccessViolation()
	{
		$req = new Request();
		$req->params->service = 'DispatcherTestService';
		$req->params->method = 'secret';
		$d = new Dispatcher();
		$d->dispatch($req);
		$expected = array('status' => 'FAIL', 'type' => 'Zita\\DispatcherException', 'errno' => \Zita\DISPATCHER_EXCEPTION_BASE + \Zita\DISPATCHER_ERROR_METHOD_ACCESS, 'msg' => 'Method not accessible.');
		$this->expectOutputString(var_export($expected, true));
	}
	
	public function testMissingParam()
	{
		$req = new Request();
		$req->params->service = 'DispatcherTestService';
		$req->params->method = 'hello';
		$d = new Dispatcher();
		$d->dispatch($req);
		$expected = array('status' => 'FAIL', 'type' => 'Zita\\DispatcherException', 'errno' => \Zita\DISPATCHER_EXCEPTION_BASE + \Zita\DISPATCHER_ERROR_METHOD_PARAM, 'msg' => 'Missing parameters.');
		$this->expectOutputString(var_export($expected, true));
	}

	public function testNormal()
	{
		$req = new Request();
		$name = 'John';
		$req->params->service = 'DispatcherTestService';
		$req->params->method = 'hello';
		$req->params->name = $name;
		$d = new Dispatcher();
		$response = $d->dispatch($req);
		$this->assertEquals("Hello $name", $response->body);
	}
}