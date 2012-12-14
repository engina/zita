<?php
require_once('Zita/Core.php');

use Zita\Request;
use Zita\Response;
use Zita\Service;
use Zita\Dispatcher;
use Zita\IAnnotation;
use Zita\Core;

class TestAnnotation implements IAnnotation
{
	private $cfg;
	private $pre;
	private $post;
	
	public function __construct($cfg)
	{
		$this->cfg = $cfg;
		list($this->pre, $this->post) = explode(',', $cfg);
	}
	
	public function preProcess(Request $req, Response $resp, Service $service = null, $method = null)
	{
		$req->params->name .= $this->pre;
	}
	
	public function postProcess(Request $req, Response $resp, Service $service = null, $method = null)
	{
		$resp->body .= $this->post;
	}
}

/**
 * @Test prev,post
 */
class AnnotationsTestService extends Service
{
	public function hello($name)
	{
	    $this->response->body = "Hello $name";
	}
	
	/**
	 * @InvalidAnnotation
	 */
	public function hola()
	{
	}
}

class AnnotationsTest extends PHPUnit_Framework_TestCase
{
	public function testInvalidAnnotation()
	{
		$name = 'John';
		$req = new Request();
		$req->params->service = 'AnnotationsTestService';
		$req->params->method = 'hello';
		$req->params->name = $name;
		$d = new Dispatcher();
		$response = $d->dispatch($req);
		$this->assertEquals("Hello $name".'prevpost', $response->body);
	}
	
	public function testAnnotation()
	{
		$name = 'John';
		$req = new Request();
		$req->params->service = 'AnnotationsTestService';
		$req->params->method = 'hola';
		$req->params->name = $name;
		$d = new Dispatcher();
		$resp = $d->dispatch($req);
		$expected = array('status' => 'FAIL', 'type' => 'Zita\ClassNotFoundException', 'errno' => 1000, 'msg' => "Could not load class 'InvalidAnnotationAnnotation'");
		$this->assertEquals(var_export($expected, true), $resp->body);
	}
}