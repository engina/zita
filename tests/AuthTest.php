<?php
require_once('Zita/Core.php');

use Zita\Core;
use Zita\Dispatcher;
use Zita\Request;
use Zita\Response;
use Zita\Security\IUser;
use Zita\Security\IAuthenticator;
use Zita\Security\CouldNotAuthenticateException;

class MyUser implements IUser
{
    private $id;
    private $pass;
    private $roles;

    public function __construct($id, $pass, array $roles)
    {
        $this->id    = $id;
        $this->pass  = $pass;
        $this->roles = $roles;
    }

    public static function getByIdentifier($id)
    {
        return null;
    }

    public function getIdentifier()
    {
        return $this->id;
    }

    public function getPassword()
    {
        return $this->pass;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize(array('id'=>$this->getIdentifier(), 'roles'=>$this->getRoles()));
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return mixed the original value unserialized.
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $this->__construct($data['id'], '', $data['roles']);
    }
}

class MyAuthenticator implements IAuthenticator
{
    private $users;
    public function __construct()
    {
        $this->users = array(array('id' => 'john', 'password' => 'mysecret', 'roles' => array('ADMIN', 'MODERATOR', 'USER')));
    }

    /**
     * @param $object sometimes a username and password, sometimes an authentication token (Facebook)
     * @return IUser
     */
    public function authenticate($data)
    {
        $u = null;
        foreach($this->users as $user)
        {
            if($user['id'] == $data['identifier'] && $user['password'] == $data['password'])
            {
                $u = $user;
                break;
            }
        }
        if($u == null)
            throw new CouldNotAuthenticateException();
        return new MyUser($u['id'], $u['password'], $u['roles']);
    }
}

class AuthTestService extends Zita\Security\AuthServiceBase
{

    function __construct(Request $req, Response $resp, \Zita\Dispatcher $dispatcher)
    {
        parent::__construct($req, $resp, $dispatcher);
        $this->addAuthenticator(new MyAuthenticator());
    }
}

class SecureService extends \Zita\Service
{
    public function hello()
    {
        $this->response->body .= 'Hello '.$this->request->user->getIdentifier();
    }
}

class AuthTest extends PHPUnit_Framework_TestCase
{
    public function testMethods()
    {
        $d = new Dispatcher();
        $req = new Request();
        $req->params->service = 'AuthTest';
        $req->params->method  = 'authmethods';
        $resp = $d->dispatch($req);
        $expected = array('MyAuthenticator');
        $this->assertEquals($expected, $resp->body);
    }

    public function testCouldNotAuth()
    {
        $d = new Dispatcher();
        $req = new Request();
        $req->params->service = 'AuthTest';
        $req->params->method  = 'auth';
        $req->params->authenticator = 'MyAuthenticator';
        $req->params->data    = array('identifier' => 'john', 'password' => 'invalid_password');
        $resp = $d->dispatch($req);
        $expected = array('status' => 'FAIL',
                          'type'   => 'Zita\Security\CouldNotAuthenticateException',
                          'errno'  => 3000,
                          'msg'    => 'Could not authenticate.');
        $this->assertEquals($expected, $resp->body);
    }

    public function testAuth()
    {
        $d = new Dispatcher();
        $sessionPath = Core::path(dirname(__FILE__), 'tmp', 'sessions');
        mkdir($sessionPath, 777, true);
        $d->getSessionProvider()->setPath($sessionPath);
        $req = new Request();
        $req->params->service = 'AuthTest';
        $req->params->method  = 'auth';
        $req->params->authenticator = 'MyAuthenticator';
        $req->params->data    = array('identifier' => 'john', 'password' => 'mysecret');
        $resp = $d->dispatch($req);
        $resp->body = new \Zita\ArrayWrapper($resp->body);
        $this->assertEquals('OK', $resp->body->status);
        $this->assertTrue(strlen($resp->body->access) > 8);
        $access = $resp->body->access;

        // Now another request with the access token we've just acquired and see if the service rememebrs who we are
        $req = new Request();
        $req->params->service = 'Secure';
        $req->params->method  = 'hello';
        $req->params->access  = $access;
        $resp = $d->dispatch($req);
        $this->assertEquals('Hello john', $resp->body);
    }
}