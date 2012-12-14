<?php
namespace Zita;

abstract class Session extends ArrayWrapper
{
	private $sid;
	
	public function getSid()
	{
		return $this->sid;
	}
	
	abstract public static function create();
	abstract public static function load();
	
	abstract public function save();
}