<?php
/**
 * User: Engin
 * Date: 17.12.2012
 * Time: 02:25
 */
require_once('Zita/Core.php');
use Zita\ISessionProvider;

class SessionTestBase extends PHPUnit_Framework_TestCase
{
    private $sid;
    private $provider;

    public function __construct(ISessionProvider $provider)
    {
        $this->provider = $provider;
    }

    public function testCreate()
    {
        $session = $this->provider->create();
        $this->assertInstanceOf('Zita\Session', $session);
        return $session;
    }

    /**
     * @depends testCreate
     */
    public function testSave(\Zita\Session $session)
    {
        $session->test = 'Foo';
        $this->assertTrue($session->save());
        return $session->getSID();
    }

    /**
     * @depends testSave
     */
    public function testLoad($sid)
    {
        $session = $this->provider->load($sid);
        $this->assertInstanceOf('Zita\Session', $session);
        $this->assertEquals('Foo', $session->test);
        return $session;
    }

    /**
     * @depends testLoad
     */
    public function testModify(\Zita\Session $session)
    {
        $session->test = 'Bar';
        $this->assertTrue($session->save());
        $session = $this->provider->load($session->getSID());
        $this->assertEquals('Bar', $session->test);
    }

    public function testWindow()
    {
        $this->provider->setWindow(100);
        $this->assertEquals(100, $this->provider->getWindow());
    }

    public function testClean()
    {
        $this->assertTrue($this->provider->cleanup());
    }
}