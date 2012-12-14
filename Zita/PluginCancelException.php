<?php
namespace Zita;

/**
 * User: Engin
 * Date: 14.12.2012
 * Time: 09:03
 */
class PluginCancelException extends \Zita\PluginException
{
    public function __construct($msg = 'Plugin cancel request exception')
    {
        parent::__construct($msg, 1);
    }
}
