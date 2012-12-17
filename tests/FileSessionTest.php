<?php
require_once('Zita/Core.php');
use Zita\FileSessionProvider;
use Zita\Core;

require_once('SessionTestBase.php');

/**
 * User: Engin
 * Date: 17.12.2012
 * Time: 03:05
 */
class FileSessionTest extends SessionTestBase
{
    public function __construct()
    {
        parent::__construct(new FileSessionProvider(Core::path(dirname(__FILE__), 'tmp', 'sessions')));
    }
}
