<?php
namespace Zita;

const SESSION_EXCEPTION_BASE = 4000;

class SessionException extends \Zita\ZitaException
{
    public function __construct($msg, $code = 0, $prev = null)
    {
        parent::__construct(SESSION_EXCEPTION_BASE, $msg, $code, $prev);
    }
}
