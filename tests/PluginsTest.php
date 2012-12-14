<?php
require_once('api/0.1/zita/Zita/Core.php');

use \Zita\Dispatcher;
use \Zita\PluginContainer;
use \Zita\Plugin;
use \Zita\Request;
use \Zita\Response;
use \Zita\Service;
use \Zita\Core;

class FilterA extends Plugin
{
	public function preProcess(Request $req)
	{
	} 
	
	public function postProcess(Request $req, Response $resp)
	{
		$resp->body .= 'a';
	}
}

class FilterB extends Plugin
{
	public function preProcess(Request $req)
	{
	}  
	
	public function postProcess(Request $req, Response $resp)
	{
		$resp->body .= 'b';
	}
}

class FilterC extends Plugin
{
	public function preProcess(Request $req)
	{
	} 
	
	public function postProcess(Request $req, Response $resp)
	{
		$resp->body .= 'c';
	}
}

class FilterCancel extends Plugin
{
	public function preProcess(Request $req)
	{
	}  
	
	public function postProcess(Request $req, Response $resp)
	{
		return false;
	}
}

class ComplexFilterTestService extends Service
{
	/**
	 * @Filter FilterA|FilterA|FilterB|FilterC|\Zita\Filters\JsonOutput
	 */
	public function hello($name)
	{
		return new Response("Hello $name");
	}	
}

class FiltersTest extends PHPUnit_Framework_TestCase
{
	public function testAdd()
	{
		$req  = new Request();
		$resp = new Response(); 
		$e = new PluginContainer();
		$e->add(new FilterA());
		$e->postProcess($req, $resp);
		$this->assertEquals('a', $resp->body);
	}
	
	public function testMultiAdd()
	{
		$req  = new Request();
		$resp = new Response(); 
		$e = new PluginContainer();
		$e->add(new FilterA());
		$e->add(new FilterB());
		$e->add(new FilterC());
		$e->postProcess($req, $resp);
		$this->assertEquals('abc', $resp->body);
	}
	
	public function testRemove()
	{
		$req  = new Request();
		$resp = new Response(); 
		$e = new PluginContainer();
		$e->add(new FilterA());
		$b = $e->add(new FilterB());
		$e->add(new FilterC());
		$e->remove($b);
		$e->postProcess($req, $resp);
		$this->assertEquals('ac', $resp->body);
	}
	
	public function testCanceller()
	{
		$req  = new Request();
		$resp = new Response(); 
		$e = new PluginContainer();
		$e->add(new FilterA());
		$e->add(new FilterB());
		$e->add(new FilterCancel());
		$e->add(new FilterC());
		$e->postProcess($req, $resp);
		$this->assertEquals('ab', $resp->body);
	}
	
	public function testDispatcherFilter()
	{
		$req  = new Request();
		$resp = new Response(); 
		$d    = new Dispatcher();
		$name = 'John';
		$req->params->service = 'ComplexFilterTestService';
		$req->params->method  = 'hello';
		$req->params->name    = $name;
		$resp = $d->dispatch($req);
		$this->assertEquals(json_encode("Hello $name".'a'.'a'.'b'.'c'), $resp->body);
	}
}