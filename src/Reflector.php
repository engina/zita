<?php
namespace Zita;

class Reflector
{
	protected static function parseAnnotation($doccomment)
	{
		$result = array();
		$lines = explode("\n", $doccomment);
		foreach($lines as $line)
		{
			$key = null;
			$value = null; 
			$line = trim($line);
			if(substr($line, 0, 1) == '*')
				$line = substr($line, 1);
			
			$line = trim($line);
			if(substr($line, 0, 1) != '@')
				continue;
			$valueStart = strpos($line, ' ');
			if($valueStart === false)
			{
				// No value annotation
				$key = substr($line, 1);
				$value = null;
				goto push;
			}
			
			$key = trim(substr($line, 1, $valueStart));
			$value = trim(substr($line, $valueStart));
			if(strpos($value, ';') !== false)
			{
				// Multi value annotation
				$values = explode(';', $value);
				$value = array();
				foreach($values as $v)
				{
					$v = trim($v);
					if(strpos($v, '=') !== false)
					{
						// Key value pair
						list($value_key, $value_value) = explode('=', $v);
						$value[trim($value_key)] = trim($value_value);
					}
					else
					{
						// Just a key
						array_push($value, $v);
					}
				}
			}
			else
			{
				// Single value annotation
				if($value === 'true')
					$value = true;
				if($value === 'false')
					$value = false;
				if($value === 'null')
					$value = null;
			}
			
			push:
			$result[$key] = $value;
		}
		return $result;
	}
	
	/**
	 * Parses annotations of a class.
	 * 
	 * Acceptable formats
	 * Simple key value pair. Result will be array('Foo' => 'bar')
	 * @Foo    bar
	 * Special values such as true, false and null are converted to primative types from strings. Result will be like array('Public', true)
	 * @Public true
	 * Multiple values are also possible with the given format. Result will be like array('Zor' => array('foo' => 'bar', 'hell' => 'no'))
	 * @Zor    foo=bar;hell=no
	 * No value annotations are also possible, the value will be set to null. Result will be like array('NoParam' => null);
	 * @NoParam
	 * 
	 * @param $class name of the class
	 * @return an array of parsed annotations
	 */
	public static function getClassAnnotation($class)
	{
		$r = new \ReflectionClass($class);
		return self::parseAnnotation($r->getDocComment()); 
	}
	
	/**
	 * Parses annotation information from the method
	 * @param  $class  name of the class
	 * @param  $method name of the method
	 * @return an array of parsed annotations
	 * @see    getClassAnnotation($class)
	 */
	public static function getMethodAnnotation($class, $method)
	{
		$r = new \ReflectionClass($class);
		$m = $r->getMethod($method);
		return self::parseAnnotation($m->getDocComment());
	}
	
	/**
	 * Extracts annotations from class, then from method. Annotations of the method overrides the class annotations.
	 * @param $class  name of the class
	 * @param $method name of the method
	 * @return an array of parsed annotations
	 * @see    getClassAnnotation($class)
	 */
	public static function getMergedMethodAnnotation($class, $method)
	{
		$ca = self::getClassAnnotation($class);
		$ma = self::getMethodAnnotation($class, $method);
		return array_merge($ca, $ma);
	}
}