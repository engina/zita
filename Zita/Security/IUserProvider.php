<?php
namespace Zita\Security;

/**
 * User: Engin
 * Date: 18.12.2012
 * Time: 03:06
 */
interface IUserProvider
{
    /*
     * @return IUser to IAuthenticator so that it can verify credentials.
     *         returns null if user cannot be found by the given identifier.
     */
    public function getByIdentifier($id);
}
