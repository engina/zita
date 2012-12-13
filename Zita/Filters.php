<?php
namespace Zita;

class Filters
{
	private $callbacks = array();
	
	public function add(Filter $c)
	{
		$this->callbacks[] = $c;
		return $c;
	}
	
	public function remove(Filter $c)
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