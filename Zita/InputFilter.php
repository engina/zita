<?php
namespace Zita;

/**
 * Input filters which are meant to be used with @InputFilter should derive from this class.
 *
 * You can also derive from IFilter itself but you have to put in a stub for preProcess() for yourself then.
 */
abstract class InputFilter implements IFilter
{
    abstract function __construct($paramString);

    abstract function preProcess(Request $req, Response $resp, Dispatcher $dispatcher, Service $service, $method);

    function postProcess(Request $req, Response $resp, Dispatcher $dispatcher, Service $service, $method)
    {
        // Left blank intentionally.
    }
}
