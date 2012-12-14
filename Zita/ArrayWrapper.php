<?php
namespace Zita;

class ArrayWrapper
{
	private $m_arr = array();
	
	public function __construct(array $arr = array())
	{
		$this->m_arr = $arr;
	}
	
	public function __get($name)
	{
		if(!isset($this->m_arr[$name]))
		{
			return null;
		}
		return $this->m_arr[$name];
	}
	
	public function __set($name, $value)
	{
		$this->m_arr[$name] = $value;
	}
}

?>