<?php
namespace Zita\Plugins;

use Zita\Plugin;
use Zita\Request;
use Zita\Response;

class TestPlugin extends Plugin
{
	public function test($name)
	{
		return new Response("Hello $name, I'm a plugin");
	}


	public function preProcess(Request $req)
	{
		
	}
	
	public function postProcess(Request $req, Response $resp)
	{
		
	}
}