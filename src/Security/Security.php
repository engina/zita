<?php
namespace Zita\Security;

class Security
{
	/**
	 * List of algorithms in order of preference
	 */
	public static $PREFERRED_ALGOS = array('sha512', 'sha256', 'sha1', 'md5');
	
	/**
	 * Picks the most preferred available algorithm from Security::$PREFERRED_ALGOS 
	 */
	public static function algo()
	{
		$algo = null;
		$algos = hash_algos();
		foreach(self::$PREFERRED_ALGOS as $a)
		{
			if(in_array($a, $algos))
			{
				$algo = $a;
				break;
			}
		}
		if($algo == null)
			throw new \Exception('No preferred hashing algorithms are available');
		return $algo;
	}
}