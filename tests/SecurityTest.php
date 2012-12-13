<?php
require_once('api/0.1/zita/Zita/Core.php');

use Zita\Request;
use Zita\Dispatcher;
use Zita\Controller;
use Zita\Response;
use Zita\Core;

/**
 * @Secure role=admin
 */
class SecurityTestController extends Controller
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
		$req->params->c = 'SecurityTestController';
		$req->params->m = 'hello';
		$d = new Dispatcher();
		$d->dispatch($req);
		$expected = array('errno' => 101, 'msg' => "Authentication is required.");
		$this->expectOutputString(var_export($expected, true));
	}
}
