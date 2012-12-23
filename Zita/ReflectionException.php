<?php
namespace Zita;

const REFLECTION_EXCEPTION_BASE = 7000;

class ReflectionException extends ZitaException
{
    public function __construct($msg, $code = 0, $prev = null)
    {
        parent::__construct(REFLECTION_EXCEPTION_BASE, $msg, $code, $prev);
    }
}