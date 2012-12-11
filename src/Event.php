<?php
namespace Zita;

class Event
{
	private $callbacks = array();
	
	public function add(callable $c)
	{
		$this->callbacks[] = $c;
	}
	
	public function remove(callable $c)
	{
		$key = array_search($c, $this->callbacks);
		if($key === false)
			throw new Exception('Event handler not found');
		unset($this->callbacks[$key]);
	}
	
	public function fire($obj)
	{
		foreach($this->callbacks as $callback)
		{
			if(call_user_func($callback, $obj) === false) break;
		}
	}
}