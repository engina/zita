<?php
namespace Zita;

// require('Debug.php');

define('DS', DIRECTORY_SEPARATOR);
define('PS', PATH_SEPARATOR);

define('ZITA_ROOT', dirname(dirname(__FILE__)));
define('APP_ROOT', str_replace('/', DS, dirname($_SERVER['SCRIPT_FILENAME'])));

Core::addServicePath(Core::path(APP_ROOT, 'Controllers'));
Core::addIncludePath(ZITA_ROOT);
Core::addIncludePath(Core::path(ZITA_ROOT, 'Zita'));
Core::addIncludePath(Core::path(ZITA_ROOT, 'Zita', 'Annotations'));
Core::addIncludePath(Core::path(ZITA_ROOT, 'Zita', 'Filters'));
Core::addIncludePath(Core::path(ZITA_ROOT, 'Zita', 'Plugins'));
Core::addIncludePath(APP_ROOT);
Core::addIncludePath(Core::path(APP_ROOT, 'Services'));
Core::addIncludePath(getcwd());

function classloader($class, $paths = null)
{
	$class = str_replace('\\', DS, $class);

	if($paths == null)
		$paths = get_include_path();
	
	$paths = explode(PS, $paths);
	foreach($paths as $path)
	{
		$test = $path.DS.$class.'.php';
		if(!is_readable($test)) continue;
		require_once($test);
		return;
	}
}

\spl_autoload_register('Zita\classloader');

class Core
{
	private static $SERVICE_PATHS;
	
	public static function addServicePath($path)
	{
		self::$SERVICE_PATHS = $path.PS.self::$SERVICE_PATHS;
	}
	
	public static function removeServicePath($path)
	{
		$paths = self::getServicePaths();
		$key   = array_search($path, $paths);
		if($key === false) return false;
		unset($paths[$key]);
		self::$SERVICE_PATHS = implode(PS, $paths);
		return true;
	}
	
	public static function getServicePaths()
	{
		return explode(PS, self::$SERVICE_PATHS);
	}
	
	public static function addIncludePath($path)
	{
		set_include_path($path.PS.get_include_path());
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
		
		if(class_exists($class, false))
		{
			return $class;
		}
		
		$cls = get_declared_classes();
		
		foreach($cls as $cl)
		{
			$match = substr($cl, -strlen($class));
			if($match === $class)
			{
				return "\\$cl";
			}
		}
		return null;
	}

    /**
     * Variable argument function, joins each parameter with the platform specific DIRECTORY_SEPARATOR.
     * <code>
     * $str = Core::path('foo', 'bar', 'faz', 'baz'); // On Windows: 'foo\bar\faz\baz' On other platforms: 'foo/bar/faz/baz'
     * </code>
     * @param variable $args
     * @internal param \Zita\variable $args argument list
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
	 * If it is not already loaded tries to load it from get_include_paths()
	 * 
	 * @param $class class path such MyNamespace\Foo
	 * @return full class path \Company\MyNamespace\Foo if found, false otherwise
	 */
	public static function load($class, $paths = null)
	{
		$fullPath = self::resolveClass($class);
		if($fullPath != null)
			return $fullPath;
		// Class does not exist
		if(is_array($paths))
			$paths = implode(PS, $paths);
		classloader($class, $paths);
		$path = self::resolveClass($class);
		if($path === null)
			throw new ClassNotFoundException("Could not load class '$class'");
		return $path;
	}
}

?>
