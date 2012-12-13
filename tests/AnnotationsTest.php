<?php
require_once('api/0.1/zita/Zita/Core.php');

use Zita\Request;
use Zita\Response;
use Zita\Controller;
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
	
	public function preProcess(Request $req, Response $resp, Controller $controller = null, $method = null)
	{
		$req->params->name .= $this->pre;
	}
	
	public function postProcess(Request $req, Response $resp, Controller $controller = null, $method = null)
	{
		$resp->body .= $this->post;
	}
}

/**
 * @Test prev,post
 */
class AnnotationsTestController extends Controller
{
	public function hello($name)
	{
		return new Response("Hello $name");
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
		$req->params->c = 'AnnotationsTestController';
		$req->params->m = 'hello';
		$req->params->name = $name;
		$d = new Dispatcher();
		$d->dispatch($req);
		$this->expectOutputString("Hello $name".'prevpost');
	}
	
	public function testAnnotation()
	{
		$name = 'John';
		$req = new Request();
		$req->params->c = 'AnnotationsTestController';
		$req->params->m = 'hola';
		$req->params->name = $name;
		$d = new Dispatcher();
		$d->dispatch($req);
		$expected = array('errno' => 0, 'msg' => "Could not load class 'InvalidAnnotationAnnotation'");
		$this->expectOutputString(var_export($expected, true));
	}
}