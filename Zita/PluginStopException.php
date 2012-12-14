<?php
namespace Zita;

/**
 * User: Engin
 * Date: 14.12.2012
 * Time: 09:03
 */
class PluginStopException extends \Zita\PluginException
{
    public function __construct($msg = 'Plugin stop request exception')
    {
        parent::__construct($msg, 0);
    }
}
