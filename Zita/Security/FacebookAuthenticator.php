<?php
namespace Zita\Security;

use Zita\Core;
use Zita\Security\IUserProvider;

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
    private $provider;

    public function __construct(IUserProvider $provider, $appid, $secret)
    {
        $this->facebook = new \Facebook(array('appId' => $appid, 'secret' => $secret));
        $this->provider = $provider;
    }

	public function authenticate($identifier, $data)
	{
        $_REQUEST['signed_request'] = $data['signed_request'];
        $me = $this->facebook->api('/me');
        if($me['id'] != $identifier)
            return null;
        return $this->provider->getByIdentifier($me);
	}
}