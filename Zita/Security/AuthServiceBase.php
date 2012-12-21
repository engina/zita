<?php
namespace Zita\Security;

use \Zita\Response;
use \Zita\Service;
use \Zita\ISessionProvider;

abstract class AuthServiceBase extends Service
{
	private $authenticatorsDict = array();

	protected function addAuthenticator(IAuthenticator $auth, $name = null)
	{
        $className = basename(get_class($auth));
        $suffix    = 'Authenticator';
        if(strlen($className) > strlen($suffix))
        {
            $className = str_replace($suffix, '', $className);
        }
		$authenticatorsName = $name == null ? $className: $name;
		$this->authenticatorsDict[$authenticatorsName] = $auth;
	}

    /**
     * @param $authenticator name of the authenticator to use, available authenticators are retrieved via authmethods()
     * @param $identifier user identifier, such as; username, email
     * @param array $data content of the $data depends on the IAuthenticator used.
     * @param bool $remember if has any value, a remember token will be returned, the token can used for automatic
     *        logging-in in further sessions. Remember token must be provided in data[remember]
     *        Thanks to separation of concerns we can have a IAuthenticator agnostic remember me functionality.
     *        Provided that IAuthenticator authenticates user by $data, remember me functionality stores $data
     *        in the client in an encrypted form. So user can use the encrypted token to re-login later.
     *
     *        In a username password scenario, this means encrypting the password on the server side with a secret key
     *        and storing it in the client. Even the client herself cannot decrypt the data.
     *
     *        Remember me won't work on IAuthenticators which does not rely on $data, such as Facebook.
     * @throws SecurityException
     */
    public function auth($authenticator, $identifier, array $data, $remember = false)
	{
		if(!isset($this->authenticatorsDict[$authenticator]))
			throw new SecurityException("Invalid authenticator '$authenticator'");

        if(isset($data['remember']))
        {
            $t = Security::decrypt(Security::base64UrlDecode($data['remember']));
            $d = unserialize($t);
            if($d === false)
                throw new SecurityException("Invalid remember");
            $data = $d;
        }

		$user = $this->authenticatorsDict[$authenticator]->authenticate($identifier, $data);
		if($user == null)
			throw new SecurityException("Could not authenticate client.");

        $session = $this->dispatcher->getSessionProvider()->create();
        $session->user = $user;

		$this->response->body = array('status' => 'OK',
                                      'auth'   => $session->getSID());
        if($remember)
        {
            // This provides IAuthenticator agnostic remember me functionality.
            $this->response->body['remember'] = Security::base64UrlEncode(Security::encrypt(serialize($data)));
        }
	}

    /**
     * Prints the available authenticator names.
     *
     * Authenticator names, if not given manually, are generated from class names.
     *
     * These names does NOT include namespace path and they are only the class names.
     *
     * If the class name is suffixed with Authenticator, it will be removed.
     *
     * i.e. Zita\Security\GenericAuthenticator will be named Generic.
     *
     * User must use one of these available authorizors with the AuthServiceBase->auth() method.
     */
    public function authMethods()
	{
		$this->response->body = array_keys($this->authenticatorsDict);
	}
}