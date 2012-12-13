<?php
namespace Zita\Security;

interface IAuthenticator
{
	/**
	 * @param $object sometimes a username and password, sometimes an authentication token (Facebook)
	 * @return IUser 
	 */
	public function authenticate($object);
}

?>