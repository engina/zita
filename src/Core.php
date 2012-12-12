<?php
namespace Zita;

require('Debug.php');
require('Exception.php');

define('ZITA_ROOT', dirname(__FILE__));
define('APP_ROOT', dirname($_SERVER['SCRIPT_FILENAME']));

define('DS', DIRECTORY_SEPARATOR);
define('PS', PATH_SEPARATOR);

class Core
{
	public static $INCLUDES = array();
	
	public static function init()
	{
		set_include_path(ZITA_ROOT.PS.get_include_path());
		self::$INCLUDES[] = ZITA_ROOT;
		self::$INCLUDES[] = ZITA_ROOT.DS.'Filters';
		self::$INCLUDES[] = ZITA_ROOT.DS.'Validators';
		self::$INCLUDES[] = ZITA_ROOT.DS.'Annotations';
		self::$INCLUDES[] = APP_ROOT;
	}
	
	/**
	 * Returns a full class path of a partial class name. Imagine there are these
	 * classes available
	 * - B
	 * - NS1\A
	 * - NS1\SubNS\B
	 * - NS2\A
	 * - NS2\B
	 * 
	 * Looking up for A will return \NS1\A as it is the first known class mathing the path
	 * Looking up for B will return \B
	 * Looking up for SubNS\B will return \NS1\SubNS\B
	 * Looking up for NS2\A will return \NS2\A
	 * 
	 * @param $class partial class path
	 * @return string|NULL
	 */
	public static function resolveClass($class)
	{
		if($class{0} != "\\")
			$class = "\\$class";
		
		$cls = get_declared_classes();
		if(class_exists($class))
			return $class;
		
		foreach($cls as $cl)
		{
			$match = substr($cl, -strlen($class));
			if($match === $class)
				return "\\$cl";
		}
		return null;
	}
	
	/**
	 * Variable argument function, joins each parameter with the platform specific DIRECTORY_SEPARATOR.
	 * <code>
	 * $str = Core::path('foo', 'bar', 'faz', 'baz'); // On Windows: 'foo\bar\faz\baz' On other platforms: 'foo/bar/faz/baz'
	 * </code>
	 * @param args variable argument list
	 * @return joined path of arguments
	 */
	public static function path($args)
	{
		$num = func_num_args();
		if($num == 0) return '';
		if($num == 1) return func_get_arg(0);
		$str = func_get_arg(0);
		for($i = 1; $i < $num; $i++)
			$str .= DS . func_get_arg($i);
		return $str;
	}
	
	/**
	 * Tries to find the class you want.
	 * 
	 * If it is not already loaded tries to load it from paths in Core::$INCLUDES
	 * 
	 * @param $class class path such MyNamespace\Foo
	 * @return full class path \Company\MyNamespace\Foo if found, false otherwise
	 */
	public static function load($class)
	{
		// Try to resolve class now
		$fullPath = self::resolveClass($class);
		if($fullPath != null)
			return $fullPath;
		
		// The class does not exist now, try to load it.
		foreach(self::$INCLUDES as $i)
		{
			$tokens = explode("\\", $class);
			array_unshift($tokens, $i);
			$path = call_user_func_array("\\Zita\\Core::path", $tokens).'.php';
			if(!is_readable($path)) continue;
			require_once($path);
			
			// A file matching the class is loaded try to resolve it again
			$fullPath = self::resolveClass($class);
			if($fullPath == null)
				return false;
			return $fullPath;
		}
		
		return false;
	}
	
	public static function normalize($name)
	{
		return ucfirst(strtolower($name));
	}
}

?>
