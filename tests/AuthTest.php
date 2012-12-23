<?php
require_once('Zita/Core.php');

use Zita\Core;
use Zita\Dispatcher;
use Zita\Request;
use Zita\Response;
use Zita\Security\IUser;
use Zita\Security\IUserProvider;
use Zita\Security\GenericAuthenticator;
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

    public function getIdentifier()
    {
        return $this->id;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function serialize()
    {
        return serialize(array('id'=>$this->getIdentifier(), 'roles'=>$this->getRoles()));
    }

    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $this->__construct($data['id'], '', $data['roles']);
    }

    /**
     * This can be used for complex validations.
     *
     * Maybe you are using a database schema which allows a single role storage for a single user, such as ENUM fields.
     *
     * Then you can use this hasRole method to implement role hierarchies. Such as, hasRole('User') can return true
     * for role Admin.
     */
    public function hasRole($role)
    {
        return in_array($role, $this->getRoles());
    }

    public function verifyCredentials($data)
    {
        return $this->pass === $data['password'];
    }
}

class MyUserProvider implements IUserProvider
{
    private $users;
    public function __construct()
    {
        $this->users = array(
                              array('id' => 'john', 'password' => 'mysecret1', 'roles' => array('MODERATOR', 'USER')),
                              array('id' => 'jane', 'password' => 'mysecret2', 'roles' => array('ADMIN','MODERATOR', 'USER')),
                              array('id' => 'dave', 'password' => 'mysecret3', 'roles' => array('MODERATOR', 'USER')),
                              array('id' => 'nate', 'password' => 'mysecret4', 'roles' => array('USER')),
        );
    }

    public function getByIdentifier($id)
    {
        foreach($this->users as $user)
        {
            if($user['id'] == $id)
                return new MyUser($id, $user['password'], $user['roles']);
        }
        return null;
    }
}

class AuthTestService extends Zita\Security\AuthServiceBase
{

    function __construct(Request $req, Response $resp, \Zita\Dispatcher $dispatcher)
    {
        parent::__construct($req, $resp, $dispatcher);
        $this->addAuthenticator(new GenericAuthenticator(new MyUserProvider()));
    }
}

/*
 * \Zita\Service already has an @Secure annotation which just enables authentication mechanisms for all services
 * unless "@Secure off" is provided in the derived services.
 *
 * Authentication service, which is enabled by default, processes $this->request->params->access and gets user associated with it
 * and places it in $this->request->user which is an IUser object.
 *
 * If $this->request->user is null, the user is not using an access point hence it is an anonymous access.
 */
class SecureService extends \Zita\Service
{
    /**
     * Allows any authenticated user.
     * @Secure allow=anonymous;deny=none;order=allow,deny
     */
    public function hello()
    {
        $this->response->body .= 'Hello '.$this->request->user->getIdentifier();
    }

    /**
     * @Secure allo=x;foo=bar
     */
    public function faultyParams()
    {

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
        $expected = json_encode(array('Generic'));
        $this->assertEquals($expected, $resp->body);
    }

    public function testCouldNotAuth()
    {
        $d = new Dispatcher();
        $req = new Request();
        $req->params->service = 'AuthTest';
        $req->params->method  = 'auth';
        $req->params->authenticator = 'Generic';
        $req->params->identifier    = 'john';
        $req->params->data    = array('password' => 'invalid_password');
        $resp = $d->dispatch($req);
        $expected = array('status' => 'FAIL',
                          'type'   => 'Zita\Security\CouldNotAuthenticateException',
                          'errno'  => 3000,
                          'msg'    => 'Could not authenticate.');
        $this->assertEquals(json_encode($expected), $resp->body);
    }

    public function testAuth()
    {
        $d = new Dispatcher();
        $sessionPath = Core::path(dirname(__FILE__), 'tmp', 'sessions');
        if(!is_dir($sessionPath))
            mkdir($sessionPath, 777, true);
        $d->getSessionProvider()->setPath($sessionPath);
        $req = new Request();
        $req->params->service       = 'AuthTest';
        $req->params->method        = 'auth';
        $req->params->authenticator = 'Generic';
        $req->params->identifier    = 'john';
        $req->params->data          = array('password' => 'mysecret1');
        $req->params->remember      = 'true';
        $req->params->type          = 'raw';
        $resp = $d->dispatch($req);
        $resp->body = new \Zita\ArrayWrapper($resp->body);
        $this->assertEquals('OK', $resp->body->status);
        $this->assertNotEmpty($resp->body->auth);
        $this->assertNotEmpty($resp->body->remember);
        $remember = $resp->body->remember;
        $access   = $resp->body->auth;

        // Now another request with the access token we've just acquired and see if the service rememebrs who we are
        $req = new Request();
        $req->params->service = 'Secure';
        $req->params->method  = 'hello';
        $req->params->auth    = $access;
        $resp = $d->dispatch($req);
        $this->assertEquals(json_encode('Hello john'), $resp->body);

        $req = new Request();
        $req->params->service       = 'AuthTest';
        $req->params->method        = 'auth';
        $req->params->authenticator = 'Generic';
        $req->params->identifier    = 'john';
        $req->params->data          = array('remember' => $remember);
        $req->params->type          = 'raw';
        $resp = $d->dispatch($req);
        $resp->body = new \Zita\ArrayWrapper($resp->body);
        $this->assertEquals('OK', $resp->body->status);
        $this->assertNotEmpty($resp->body->auth);

        $req = new Request();
        $req->params->service       = 'Secure';
        $req->params->method        = 'faultyParams';
        $req->params->type          = 'raw';
        $resp = $d->dispatch($req);

        $expected = array('status' => 'FAIL',
                        'type'   => 'Zita\ReflectionException',
                        'errno'  => 7000,
                        'msg'    => 'Zita\Annotations\SecureAnnotation::__construct() requires parameter: allow');
        $this->assertEquals($expected, $resp->body);
    }
}