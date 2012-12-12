<?php
namespace Zita\Security;

require_once('Response.php');

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
	}
	
	public function authmethods()
	{
		return new Response(array_keys($this->authenticatorsDict));
	}
	
	public function create()
	{
		
	}	
}