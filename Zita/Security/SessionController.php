<?php
namespace Zita\Security;

use \Zita\Response;
use \Zita\Controller;

abstract class SessionController extends Controller
{
	private $authenticatorsDict = array();
	
	protected function addAuthenticator(IAuthenticator $auth, $name = null)
	{
		$authenticatorsName = $name == null ? get_class($auth) : $name;
		$this->authenticatorsDict[$authenticatorsName] = $auth;
	}
	
	public function login($authenticator, $data)
	{
		if(!isset($this->authenticatorsDict[$authenticator]))
			throw new \Exception("Invalid authenticator '$authenticator'");
		
		$user = $this->authenticatorsDict[$authenticator]->authenticate($data);
		if($user == null)
			throw new \Exception("Not authorized");
		return new Response(array("status" => "ok"));
	}
	
	public function authmethods()
	{
		return new Response(array_keys($this->authenticatorsDict));
	}
	
	public function create()
	{
		
	}	
}