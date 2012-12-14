<?php
namespace Zita;

const PLUGIN_EXCEPTION_BASE = 3000;

/**
 * User: Engin
 * Date: 14.12.2012
 * Time: 08:59
 */
class PluginException extends ZitaException
{
    public function __construct($msg, $code = 0, $prev = null)
    {
        parent::__construct(PLUGIN_EXCEPTION_BASE, $msg, $code, $prev);
    }
}
