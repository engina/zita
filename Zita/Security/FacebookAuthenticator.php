<?php
namespace Zita\Security;

use Zita\Core;

// Facebook stuff
Core::addIncludePath(Core::path(ZITA_ROOT, 'Zita', 'vendors', 'facebook-php-sdk', 'src'));
require_once Core::path(ZITA_ROOT, 'Zita', 'vendors', 'facebook-php-sdk', 'src', 'facebook.php');

// Database stuff
use PropelGenerated\UserQuery;
require_once 'propel/Propel.php';
\Propel::init("build/conf/WMI-conf.php");
Core::addIncludePath('build/classes');

class FacebookAuthenticator implements IAuthenticator
{
    private $facebook;

    public function __construct($appid, $secret)
    {
        $this->facebook = new \Facebook(array('appId' => $appid, 'secret' => $secret));
    }

	public function authenticate($identifier, $data)
	{
        $me = $this->facebook->api('/me');
        if($me['id'] != $identifier)
            return null;
        return UserQuery::create()->findByFbid($identifier)->getFirst();
	}
}