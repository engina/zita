<?php
namespace Zita\Security;

use Zita\Security\IAuthenticator;
use Zita\Security\CouldNotAuthenticateException;

/**
 * Generic authenticator relies on IUserProvider->getByIdentifier() and IUser->verifyCredentials().
 *
 * The $data in the GenericAuthenticator->authenticate($identifier, $data) will be passed to IUser->verifyCredentials($data).
 *
 */
class GenericAuthenticator implements IAuthenticator
{
    private $provider;

    public function __construct(IUserProvider $provider)
    {
        $this->provider = $provider;
    }

    public function authenticate($identifier, $data)
    {
        $user = $this->provider->getByIdentifier($identifier);
        if($user === null || $user->verifyCredentials($data) === false)
            throw new CouldNotAuthenticateException();
        return $user;
    }
}
