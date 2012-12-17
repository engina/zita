<?php
namespace Zita;

/**
 * Output filters which are meant to be used with @OutputFilter should derive from this class.
 *
 * You can also derive from IFilter itself but you have to put in a stub for preProcess() for yourself then.
 */
abstract class OutputFilter implements IFilter
{
    public function __construct($paramString)
    {
        // TODO: Implement __construct() method.
    }

    public function preProcess(Request $req, Response $resp, Dispatcher $dispatcher, Service $service, $method)
    {
        // Left blank intentionally
    }

    abstract function postProcess(Request $req, Response $resp, Dispatcher $dispatcher, Service $service, $method);
}
