<?php
namespace Zita\Security;
/**
 * User: Engin
 * Date: 17.12.2012
 * Time: 06:27
 */
class CouldNotAuthorizeException extends SecurityException
{
    public function __construct($msg = 'Could not authorize.')
    {
        parent::__construct($msg, 1);
    }
}
