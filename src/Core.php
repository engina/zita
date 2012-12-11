<?php
namespace Zita;

require('Debug.php');
require('Exception.php');

define('ZITA_ROOT', dirname(__FILE__));
define('DS', DIRECTORY_SEPARATOR);

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
			throw new Exception("Class '$class' could not be loaded.", 0);
		}
		
		include($file);
	}
	
	public static function normalize($name)
	{
		return ucfirst(strtolower($name));
	}
}

?>
