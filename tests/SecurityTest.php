<?php
require_once('Zita/Core.php');

use Zita\Request;
use Zita\Dispatcher;
use Zita\Service;
use Zita\Response;
use Zita\Core;

/**
 * @Secure role=admin
 */
class SecurityTestService extends Service
{
	public function hello()
	{
		return new Response("Hello");
	}
}

class SecurityTest extends PHPUnit_Framework_TestCase
{
	public function testOne()
	{
		$req = new Request();
		$req->params->c = 'SecurityTestService';
		$req->params->m = 'hello';
		$d = new Dispatcher();
		$d->dispatch($req);
		$expected = array('errno' => 101, 'msg' => "Authentication is required.");
		$this->expectOutputString(var_export($expected, true));
	}
}
