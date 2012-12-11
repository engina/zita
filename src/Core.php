<?php
namespace Zita;

require('Debug.php');
require('Exception.php');

class Core
{
	public static function p($k)
	{
		if(isset($_REQUEST[$k])) return $_REQUEST[$k];
		return null;
	}

	public static function load($class)
	{
		$file = $class.'.php';

		if(!is_readable($file))
		{
			throw new Exception('Source file could not be loaded', 0);
		}
		
		include($file);
	}
	
	public static function normalize($name)
	{
		return ucfirst(strtolower($name));
	}
}

?>
