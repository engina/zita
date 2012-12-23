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
	
	public function preProcess(Request $req, Response $resp, Dispatcher $dispatcher, Service $service, $method )
	{
        $decoded = json_decode($req->body, true);
        foreach($decoded as $attr => $val)
        {
            $req->params->__set($attr, $val);
        }

        if($req->params->letsSkipThis === true)
            $req->handled = true;

        if($req->params->throwPreException === true)
            throw new \Exception('This is an exception raised by pre annotation processing.', 9999);
	}
	
	public function postProcess(Request $req, Response $resp, Dispatcher $dispatcher, Service $service, $method)
	{
        if($req->params->throwPostException === true)
            throw new \Exception('This is an exception raised by post annotation processing.', 9999);
        $resp->body['note'] = 'post processed';
		$resp->body = json_encode($resp->body);
	}
}

/**
 * @Test cfg=prev,post
 * Disable default output filter AutoFormatFilter as it will try to re-encode this.
 * @OutputFilter
 */
class AnnotationsTestService extends Service
{
	public function hello($name)
	{
	    $this->response->body = array("msg" => "Hello $name");
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
	public function testAnnotation()
	{
		$name = 'John';
		$req = new Request();
        $req->params->service = 'AnnotationsTest';
        $req->params->method  = 'hello';
        $req->body            = "{\"name\": \"$name\"}";
		$d = new Dispatcher();
		$response = $d->dispatch($req);
		$this->assertEquals(json_encode(array("msg" => "Hello $name", "note" => "post processed")), $response->body);
	}
	
	public function testInvalidAnnotation()
	{
		$name = 'John';
		$req = new Request();
		$req->params->service = 'AnnotationsTest';
		$req->params->method  = 'hola';
        $req->body            = "{\"name\": \"$name\"}";
		$d = new Dispatcher();
		$resp = $d->dispatch($req);
		$expected = array('status' => 'FAIL', 'type' => 'Zita\ClassNotFoundException', 'errno' => 1000, 'msg' => "Could not load class 'InvalidAnnotationAnnotation'");
		$this->assertEquals($expected, $resp->body);
	}

    public function testAnnotationSkip()
    {
        $name = 'John';
        $req = new Request();
        $req->params->service = 'AnnotationsTest';
        $req->params->method  = 'hello';
        $req->body            = "{\"name\": \"$name\"}";
        $req->params->letsSkipThis = true;
        $d = new Dispatcher();
        $response = $d->dispatch($req);
        $this->assertEquals(json_encode(array("note" => "post processed")), $response->body);
    }

    public function testAnnotationPreException()
    {
        $name = 'John';
        $req = new Request();
        $req->params->service = 'AnnotationsTest';
        $req->params->method  = 'hello';
        $req->body            = "{\"name\": \"$name\"}";
        $req->params->throwPreException = true;
        $d = new Dispatcher();
        $response = $d->dispatch($req);
        $expected = json_encode(array('status' => 'FAIL',
                                      'type'   => 'Exception',
                                      'errno'  => 9999,
                                      'msg'    => 'This is an exception raised by pre annotation processing.',
                                      'note'   => 'post processed'));
        $this->assertEquals($expected, $response->body);
    }

    public function testAnnotationPostException()
    {
        $name = 'John';
        $req = new Request();
        $req->params->service = 'AnnotationsTest';
        $req->params->method  = 'hello';
        $req->body            = "{\"name\": \"$name\"}";
        $req->params->throwPostException = true;
        $d = new Dispatcher();
        $response = $d->dispatch($req);
        $expected = array('status' => 'FAIL', 'type' => 'Exception', 'errno' => 9999, 'msg' => 'This is an exception raised by post annotation processing.');
        $this->assertEquals($expected, $response->body);
    }
}