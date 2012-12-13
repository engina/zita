<?php
namespace Zita\Security;

class PropelAuthenticator implements IAuthenticator
{
	private $userModelClass;

	public function __construct($userModelClass)
	{
		$this->userModelClass = \Zita\Core::load($userModelClass);
		$r = new \ReflectionClass($userModelClass);
		if(!$r->isSubclassOf('\Zita\Security\IUser'))
			throw new \Exception("User class '$userModelClass' should implement IUser interface");
		$rm = new \ReflectionMethod();
	}

	public function authenticate($data)
	{
		$identifier = $data['identifier'];
		$p   = $data['password'];
		$user = call_user_func(array($this->userModelClass, 'getByIdentifier'), $identifier);
		if($user == null)
			throw new Exception('Invalid user');
		$password = $user->getPassword();
		list($algo, $hashed) = explode(':', $password);
		if(hash($algo, $p) != $hashed)
			throw new \Exception('Authentication failed.');
		return $user;
	}
}