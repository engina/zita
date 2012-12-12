<?php
require_once('api/0.1/zita/src/Dispatcher.php');

use Zita\Request;
use Zita\Dispatcher;
use Zita\Controller;
use Zita\Response;

class DispatcherTestController extends Controller
{
	public function hello($name)
	{
		return new Response("Hello $name");
	}
}

class DispatcherTest extends PHPUnit_Framework_TestCase
{
	public function testInvalidController()
	{
		$req = new Request();
		$req->params->c = 'InvalidController';
		$req->params->m = 'hello';
		$d = new Dispatcher();
		$d->dispatch($req);
		$expected = array('errno' => 0, 'msg' => "Controller 'InvalidController' not found");
		$this->expectOutputString(var_export($expected, true));
	}
	
	public function testInvalidMethod()
	{
		$req = new Request();
		$req->params->c = 'DispatcherTestController';
		$req->params->m = 'hola';
		$d = new Dispatcher();
		$d->dispatch($req);
		$expected = array('errno' => 2, 'msg' => "Method not found");
		$this->expectOutputString(var_export($expected, true));
	}
	
	public function testMissingParam()
	{
		$req = new Request();
		$req->params->c = 'DispatcherTestController';
		$req->params->m = 'hello';
		$d = new Dispatcher();
		$d->dispatch($req);
		$expected = array('errno' => 0, 'msg' => "Missing parameters");
		$this->expectOutputString(var_export($expected, true));
	}

	public function testNormal()
	{
		$req = new Request();
		$name = 'John';
		$req->params->c = 'DispatcherTestController';
		$req->params->m = 'hello';
		$req->params->name = $name;
		$d = new Dispatcher();
		$d->dispatch($req);
		$expected = array('errno' => 0, 'msg' => "Missing parameters");
		$this->expectOutputString("Hello $name");
	}
}