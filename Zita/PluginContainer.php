<?php
namespace Zita;

class PluginContainer
{
	private $callbacks = array();
	
	public function add(Plugin $c)
	{
		$this->callbacks[] = $c;
		return $c;
	}
	
	public function remove(Plugin $c)
	{
		$key = array_search($c, $this->callbacks);
		if($key === false)
			throw new Exception('Event handler not found');
		unset($this->callbacks[$key]);
	}
	
	public function preProcess(Request $req, Response $resp)
	{
		foreach($this->callbacks as $callback)
		{
			if($callback->preProcess($req) === false) break;
		}
	}
	
	public function postProcess(Request $req, Response $resp)
	{
		foreach($this->callbacks as $callback)
		{
			if($callback->postProcess($req, $resp) === false) break;
		}
	}
}