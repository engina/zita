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

    public static function getSecret()
    {
        if(defined('ZITA_SECRET'))
            return ZITA_SECRET;
        error_log('ZITA_SECRET not defined. Generating one for you based on your APP_ROOT and ZITA_ROOT which are '.
                  'also detected automatically. Defining your ZITA_SECRET is strongly recommended.');
        return self::hash(APP_ROOT.ZITA_ROOT);
    }

    public static function hash($data, $algo = null)
    {
        if($algo === null)
            $algo = self::algo();
        return hash($algo, $data);
    }

    public static function encrypt($data, $key = null)
    {
        if($key === null)
            $key = self::getSecret();
        return mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $data, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND));
    }

    public static function decrypt($data, $key = null)
    {
        if($key === null)
            $key = self::getSecret();
        return mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), $data, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND));
    }

    public static function encryptText($data, $key = null)
    {
        return self::encrypt($data, $key);
    }

    public static function decryptText($data, $key = null)
    {
        return trim(self::decrypt($data, $key));
    }
    /**
     * Borrowed from facebook-php-sdk.
     *
     * Base64 encoding that doesn't need to be urlencode()ed.
     * Exactly the same as base64_encode except it uses
     *   - instead of +
     *   _ instead of /
     *   No padded =
     *
     * @param string $input base64UrlEncoded string
     * @return string
     */
    public static function base64UrlDecode($input) {
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * Borrowed from facebook-php-sdk.
     *
     * Base64 encoding that doesn't need to be urlencode()ed.
     * Exactly the same as base64_encode except it uses
     *   - instead of +
     *   _ instead of /
     *
     * @param string $input string
     * @return string base64Url encoded string
     */
    public static function base64UrlEncode($input) {
        $str = strtr(base64_encode($input), '+/', '-_');
        $str = str_replace('=', '', $str);
        return $str;
    }

}