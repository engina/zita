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
		$authenticatorsName = $name == null ? get_class($auth) : $name;
		$this->authenticatorsDict[$authenticatorsName] = $auth;
	}
	
	public function auth($authenticator, $data)
	{
		if(!isset($this->authenticatorsDict[$authenticator]))
			throw new \Exception("Invalid authenticator '$authenticator'");
		
		$user = $this->authenticatorsDict[$authenticator]->authenticate($data);
		if($user == null)
			throw new \Exception("Not authorized");

        $session = $this->dispatcher->getSessionProvider()->create();
        $session->user = $user;
		$this->response->body = (array("status" => "OK", "access" => $session->getSID()));
	}
	
	public function authMethods()
	{
		$this->response->body = array_keys($this->authenticatorsDict);
	}
}