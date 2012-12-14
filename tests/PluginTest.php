<?php
require_once('Zita/Core.php');

use Zita\Dispatcher;
use Zita\Request;
use Zita\Response;
use Zita\PluginContainer;
use Zita\Plugin;
use Zita\PluginStopException;
use Zita\PluginCancelException;
use Zita\Service;

class PlugA extends Plugin
{
    public function preProcess(Request $req, Response $resp)
    {
        if($req->params->pluginData == null)
            $req->params->pluginData = '';
        $req->params->pluginData .= 'a';
    }

    public function postProcess(Request $req, Response $resp)
    {
        $resp->body .= 'a';
    }
}

class PlugB extends Plugin
{
    public function preProcess(Request $req, Response $resp)
    {
        if($req->params->pluginData == null)
            $req->params->pluginData = '';
        $req->params->pluginData .= 'b';
    }

    public function postProcess(Request $req, Response $resp)
    {
        $resp->body .= 'b';
    }
}

class PlugC extends Plugin
{
    public function preProcess(Request $req, Response $resp)
    {
        if($req->params->pluginData == null)
            $req->params->pluginData = '';
        $req->params->pluginData .= 'c';
    }

    public function postProcess(Request $req, Response $resp)
    {
        $resp->body .= 'c';
    }
}

class PlugStop extends Plugin
{
    public function preProcess(Request $req, Response $resp)
    {
        throw new PluginStopException();
    }

    public function postProcess(Request $req, Response $resp)
    {
        throw new PluginStopException();
    }
}

class PlugCancel extends Plugin
{
    public function preProcess(Request $req, Response $resp)
    {
        throw new PluginCancelException();
    }

    public function postProcess(Request $req, Response $resp)
    {
    }
}

class PluginTestService extends Service
{
    public function hello($name)
    {
        $this->response->body = "Hello $name";
    }
}

/**
 * User: Engin
 * Date: 14.12.2012
 * Time: 08:43
 */
class PluginTest extends PHPUnit_Framework_TestCase
{
    public function testAdd()
    {
        $req  = new Request();
        $resp = new Response();
        $e = new PluginContainer();
        $e->add(new PlugA());
        $e->preProcess($req, $resp);
        $e->postProcess($req, $resp);
        $this->assertEquals('a', $resp->body);
        $this->assertEquals('a', $req->params->pluginData);
    }

    public function testMultiAdd()
    {
        $req  = new Request();
        $resp = new Response();
        $e = new PluginContainer();
        $e->add(new PlugA());
        $e->add(new PlugB());
        $e->add(new PlugC());
        $e->preProcess($req, $resp);
        $e->postProcess($req, $resp);
        $this->assertEquals('abc', $resp->body);
        $this->assertEquals('abc', $req->params->pluginData);
    }

    public function testRemove()
    {
        $req  = new Request();
        $resp = new Response();
        $e = new PluginContainer();
        $e->add(new PlugA());
        $b = $e->add(new PlugB());
        $e->add(new PlugC());
        $e->remove($b);
        $e->preProcess($req, $resp);
        $e->postProcess($req, $resp);
        $this->assertEquals('ac', $resp->body);
        $this->assertEquals('ac', $req->params->pluginData);
    }

    /**
     * @expectedException Zita\PluginException
     */
    public function testRemoveFail()
    {
        $req  = new Request();
        $resp = new Response();
        $e = new PluginContainer();
        $e->add(new PlugA());
        $b = new PlugB();
        $e->remove($b);
    }

    public function testStop()
    {
        $req  = new Request();
        $resp = new Response();
        $e = new PluginContainer();
        $e->add(new PlugA());
        $e->add(new PlugB());
        $e->add(new PlugStop());
        $e->add(new PlugC());
        $e->preProcess($req, $resp);
        $e->postProcess($req, $resp);
        $this->assertEquals('ab', $resp->body);
        $this->assertEquals('ab', $req->params->pluginData);
    }

    public function testWithService()
    {
        $req = new Request();
        $d = new Dispatcher();
        $d->pluginContainer->add(new PlugA());
        $d->pluginContainer->add(new PlugB());
        $req->params->service = 'PluginTestService';
        $req->params->method  = 'hello';
        $req->params->name    = 'John';
        $resp = $d->dispatch($req);
        $this->assertEquals('Hello Johnab', $resp->body);
        $this->assertEquals('ab', $req->params->pluginData);
    }

    public function testWithServiceStop()
    {
        $req = new Request();
        $d = new Dispatcher();
        $d->pluginContainer->add(new PlugA());
        $d->pluginContainer->add(new PlugStop());
        $d->pluginContainer->add(new PlugB());
        $d->pluginContainer->add(new PlugB());
        $req->params->service = 'PluginTestService';
        $req->params->method  = 'hello';
        $req->params->name    = 'John';
        $resp = $d->dispatch($req);
        $this->assertEquals('Hello Johna', $resp->body);
        $this->assertEquals('a', $req->params->pluginData);
    }

    public function testWithServiceCancel()
    {
        $req = new Request();
        $d = new Dispatcher();
        $d->pluginContainer->add(new PlugA());
        $d->pluginContainer->add(new PlugB());
        $d->pluginContainer->add(new PlugCancel());
        $d->pluginContainer->add(new PlugC());
        $req->params->service = 'PluginTestService';
        $req->params->method  = 'hello';
        $req->params->name    = 'John';
        $resp = $d->dispatch($req);
        $this->assertEquals('ab', $resp->body);
        $this->assertEquals('ab', $req->params->pluginData);
    }
}
