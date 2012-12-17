<?php
namespace Zita\Security;
/**
 * User: Engin
 * Date: 17.12.2012
 * Time: 06:27
 */
class CouldNotAuthenticateException extends SecurityException
{
    public function __construct($msg = 'Could not authenticate.')
    {
        parent::__construct($msg, 0);
    }
}
