<?php
require_once('api/0.1/zita/src/Dispatcher.php');

$dummy_data = array('a' => 1, 'b' => 2);

/**
 * @Filter JsonOutput
 */
class JsonOutputTestController extends \Zita\Controller
{
	public function dummyMethod()
	{
		global $dummy_data;
		return new Zita\Response($dummy_data);
	}
}

class EncodersTest extends PHPUnit_Framework_TestCase
{
	public function testJsonEncoder()
	{
		global $dummy_data;
		$d   = new Zita\Dispatcher();
		$req = new Zita\Request();
		$req->params->c = 'JsonOutputTestController';
		$req->params->m = 'dummyMethod';
		$req->params->callback = 'test';
		$resp = $d->dispatch($req);
		$this->assertEquals('application/json', $resp->headers['Content-type']);
		$this->expectOutputString('test('.json_encode($dummy_data).');');
	}
}