<?php
namespace Zita\Security;

const SECURITY_EXCEPTION_BASE = 3000;

/**
 * User: Engin
 * Date: 17.12.2012
 * Time: 06:19
 */
class SecurityException extends \Zita\ZitaException
{
    public function __construct($msg, $code = 0, $previous = null)
    {
        parent::__construct(SECURITY_EXCEPTION_BASE, $msg, $code, $previous);
    }
}