<?php
require_once('Zita/Core.php');

$dummy_data = array('a' => 1, 'b' => 2);

/**
 * @Filter JsonOutput
 */
class JsonOutputTestService extends \Zita\Service
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
		$req->params->service  = 'JsonOutputTestService';
		$req->params->method   = 'dummyMethod';
		$req->params->callback = 'test';
		$resp = $d->dispatch($req);
		$this->assertEquals('application/json', $resp->headers['Content-type']);
		$this->assertEquals('test('.json_encode($dummy_data).');', $resp->body);
	}
}