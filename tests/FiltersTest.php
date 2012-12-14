<?php
require_once('Zita/Core.php');

use \Zita\Dispatcher;
use \Zita\PluginContainer;
use \Zita\Plugin;
use \Zita\Request;
use \Zita\Response;
use \Zita\Service;
use \Zita\Core;

class FilterA extends Plugin
{
	public function preProcess(Request $req, Response $resp)
	{
	} 
	
	public function postProcess(Request $req, Response $resp)
	{
		$resp->body .= 'a';
	}
}

class FilterB extends Plugin
{
	public function preProcess(Request $req, Response $resp)
	{
	}  
	
	public function postProcess(Request $req, Response $resp)
	{
		$resp->body .= 'b';
	}
}

class FilterC extends Plugin
{
	public function preProcess(Request $req, Response $rest)
	{
	} 
	
	public function postProcess(Request $req, Response $resp)
	{
		$resp->body .= 'c';
	}
}

class FilterCancel extends Plugin
{
	public function preProcess(Request $req, Response $resp)
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
		$this->respons->body = "Hello $name";
	}	
}

class FiltersTest extends PHPUnit_Framework_TestCase
{
	public function testDispatcherFilter()
	{
		$req  = new Request();
		$d    = new Dispatcher();
		$name = 'John';
		$req->params->service = 'ComplexFilterTestService';
		$req->params->method  = 'hello';
		$req->params->name    = $name;
		$resp = $d->dispatch($req);
		$this->assertEquals(json_encode("Hello $name".'a'.'a'.'b'.'c'), $resp->body);
	}
}