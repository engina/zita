<?php
namespace Zita\Security;

interface IAuthenticator
{
	/**
     * @throws Zita\Security\CouldNotAuthenticateException
	 * @param $id   User's idenfitifer
     * @param $data Additional information, might be a password or information for a public key authentication scheme.
	 * @return IUser or throw exception CouldNotAuthenticateException.
	 */
	public function authenticate($id, $data);
}

?>