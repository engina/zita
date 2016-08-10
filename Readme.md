Zita
=====
Zita is my take on how a web service framework should be.

It is based on the ideas I've discussed [here][blog1] and supposed to be the web service implementation framework of my single-static-html-page + web-services aproach.

Design Principles
-----------------
- **No configuration files**
  <br>Configuration is annotations based.
- **No bloat**
  <br>Provide only bare minimums such as Session, Security, Input/Output filters such as JSON, XML, PHP)
- **Low maintainence**
  <br>Adding a new Service or Annotation is simply declaring a class.
- **No silent errors**
  <br>Raise errors as soon as possible and do not allow silent errors to be passed. Design strongly typed interfaces (i.e., no __call()) and let the platform (PHP, for now) raise fatal errors. If weak interfaces are used such as annotations, raise error as much as possible, i.e. you type @Filtre instead of @Filter ? It will god damn throw an exception and won't silently ignore it.

Getting Started
---------------

### Hello world service

    <?php
    require_once('Zita/Core.php');
    
    class TestService extends Zita\Service
    {
      public function hello($name)
    	{
    		$this->response->body = "Hello $name!";
    	}
    }
    
    $d = new Zita\Dispatcher();
    $d->dispatch();
    ?>


Now, see what happens when we acess this service.

Request:

    GET /api.php

Response:

    {
      "status": "FAIL",
      "type": "Zita\\DispatcherException",
      "errno": 2001,
      "msg": "Could not find service 'DefaultService'"
    }

Since, we haven't tell what service and method we are trying to access, Zita assumed we are trying to access to DefaultService::index. Well, we haven't implemented that. Let's call the service we've just implemented.
Request:

    GET api.php?service=Test&method=hello

Response:

    {
      "status": "FAIL",
      "type": "Zita\\ReflectionException",
      "errno": 7000,
      "msg": "TestService::hello() requires parameter: name"
    }

Of course! Zita is telling us TestService::hello requires a parameter. Let's fix that too.
Request:

    GET api.php?service=Test&method=hello&name=Engin

Response:

    "Hello Engin!"

Note, that the response is wrapped with quotes. It's because default output format is JSON.
The string in the response body is encoded into JSON string -- hence the quotes.

This is because the base **Service** class has the **@OutputFilter AutoFormat** annotation and Zita supports (and relies) on annotation inheritance -- more on this below.

**AutoFormat** supports **type** parameter in the request. So, let's try that.

Request:

    GET api.php?service=Test&method=hello&name=Engin&type=raw

Response:

    Hello Engin!
    
Nice. It works as expected. Now let's change our service response to a more fancy one.

      	$this->response->body = array('status' => 'OK', 'msg' => "Hello $name!");

Now, our response is not a simple string but an array. Let's send a request. 
Request:

    GET api.php?service=Test&method=hello&name=Engin

Response:

    {
      "status": "OK",
      "msg": "Hello Engin!"
    }

Zita's OutputFilter AutoFormat, by default, converts the PHP array in the response->body to JSON string. You can also try type=xml. Zita has preliminary XML support.
Request:

    GET api.php?service=Test&method=hello&name=Engin&type=xml

Response:

    <root>
       <status>OK</status>
       <msg>Hello Engin!</msg>
    </root>
    
You can also try **type=raw** again.
Request:

    GET api.php?service=Test&method=hello&name=Engin&type=raw

Response:

    {
      "status": "OK",
      "msg": "Hello Engin!"
    }

Hmm, the response is again JSON. What's going on ? Well, in this case AutoFormat filter didn't touch the
response body. The the content of the body which is a PHP array has reached up until to the moment where
the response is actually flushed to the client. If the response is not a simple string, Zita, by default,
encodes it in JSON before sending as a last resort. Because, there's no way for Zita to know what is your
intended output format.

Features
--------
Zita has a strong annotation implementation with **inheritance** support and nothing else. All of the features such as automatic output filtering and security are built on top of this annotation system itself.

### **Annotations**
-  Class-wide annotations are supported.
-  Method annotations override class-wide annotations.
-  Annotation inheritance from parent classes and methods is supported.
-  Will be run before and after service execution, giving you endless possibilities.
-  Always have contextual information like which Dispatcher, Service, Method has invoked the annotation.
-  Can throw exception in pre-processing phase for situations like Authentication errors.
-  Post-processing annotations however are not allowed to throw exceptions.
-  Exceptions thrown during pre-processing will be caught and post-processing by annotations -- which will be handy to, for instance, JSONify thrown exception.
-  Users can implement custom annotation by simply deriving from IAnnotation. Nothing else is necessary.

### Annotation Inheritance
Annotation inheritance is a strong feature. It allows you to do project-wide configurations by deriving a base class.

Getting annotations for a method simple consists of parsing annotations of the class and method. Then, method annotations override class annotations. Order of class annotations won't be modified. Newly defined annotations will be appended to the annotations list. Remember, annotations are executed in the order they are defined.

![Annotations](https://github.com/engina/zita/raw/master/docs/annotations.png)

See the [test case](https://github.com/engina/zita/blob/master/tests/Reflector2Test.php) for code in action testing the diagram above.

### **Authentication**
Current security abstraction can be found in the following diagram.

![bok](https://github.com/engina/zita/raw/master/docs/authentication.png)

A sample application code can be found below:

    <?php
    namespace WMI\Services
    
    use Zita\Security\PropelAuthenticator;
    use Zita\Security\FacebookAuthenticator;
    use Zita\Security\PdoAuthenticator;
    use Zita\Security\IUserProvider;
    use Zita\Security\AuthServiceBase;
    use Zita\Security\GenericAuthenticator;
    use Zita\Core;
    use Zita\Dispatcher;
    use Zita\Request;
    use Zita\Response;
    
    use PropelGenerated\UserQuery;
    
    require_once 'propel/Propel.php';
    \Propel::init("build/conf/WMI-conf.php");
    Core::addIncludePath('build/classes');
    
    class UserProvider implements  IUserProvider
    {
        public function getByIdentifier($id)
        {
            // This method can be called by GenericAuthenticator or FacebookAuthenticator.
            // If it is FacebookAuthenticator it will pass array object to us which contains the user information
            // retrieved from Facebook. So pick email from that object, as we'd to our normal users.
            if(is_array($id))
                $id = $id['email'];
            return UserQuery::create()->findByEmail($id)->getFirst();
        }
    }
    
    class AuthService extends AuthServiceBase
    {
        public function __construct(Request $req, Response $resp, Dispatcher $dispatcher)
      {
            parent::__construct($req, $resp, $dispatcher);
            $provider = new UserProvider();
    		$this->addAuthenticator(new FacebookAuthenticator($provider, FB_APP_ID, FB_APP_SECRET));
            $this->addAuthenticator(new GenericAuthenticator($provider));
    	}
    }
    
    ?>

As you can see IUserProvider is almost a single line implementation. The other interface we need to implement is IUser and here's the code for that.

    <?php
    
    namespace PropelGenerated;
    
    use PropelGenerated\om\BaseUser;
    
    use Zita\Security\IUser;
    
    class User extends BaseUser implements IUser
    {
        public function serialize()
        {
            return serialize($this->toArray());
        }
    
        public function setPassword($password)
        {
            $algo = \Zita\Security\Security::algo();
            parent::setPassword($algo.':'.\hash($algo, $password));
        }
    
        public function unserialize($serialized)
        {
            $this->fromArray(unserialize($serialized));
        }
    
        public function getIdentifier()
        {
            return $this->getEmail();
        }
        
        // You can verify user with any data posted.
        public function verifyCredentials($data)
        {
            list($algo, $hash) = explode(':', $this->getPassword());
            return hash($algo, $data['password']) == $hash;
        }
    
        public function getRoles()
        {
            return $this->getRoles();
        }
    
        // You can use this method to implement role hierarchies.
        public function hasRole($role)
        {
            return in_array($role, $this->getRoles());
        }
    }


IUser and IUserProvider implementations are enough to use authentication system.

### **Authorization**
Once the client retrieved the authentication token, whenever it is used in requests, the Request object will
have the associated IUser object in the Request->user property. Unless, the service explicitly does not disable
@Authorize annotation, which is enabled by default.

    class SecureService extends Service
    {
        /**
         * @Authorize authenticated
         */
        public function hello()
        {
            // Since only "authenticated" users are allowed to access this service call
            // $this->request->user is guaranteed to be non-null.
            $this->response->body = 'Hello '.$this->request->user->getIdentifier().'!';
        }
        
        /**
         * @Authorize admin, moderator
         */
        public function edit($id)
        {
        }
    }

**@Authorize** accepts comma separated role names which are to be verified by IUser::hasRole(). There are two
predefined special roles **all** and **authenticated**. One allows anonymous access and latter allows any authenticated
user to access respectively.

Base class **Service** has **@Authorize all** defined for it so all the services by default allows all access.

See [AuthTest](https://github.com/engina/zita/blob/master/tests/AuthTest.php) for code examples.

No configuration files
--------------------------
Zita does not like configuration files. Everything you need to modify is an interface that you can implement and replace functionality. You want to authorize users with Facebook ? Derive from IAuthenticator and IUser and you are good to go.

Probably there'll be a few default Authenticator implementations such as PdoAuthenticator and FacebookAuthenticator.

Custom Annotations
--------------------------

    
    class TestAnnotation implements IAnnotation
    {
    	public function __construct($cfg)
    	{
    	}
    	
    	public function preProcess(Request $req, Response $resp, Dispatcher $dispatcher, Service $service, $method )
    	{
            $decoded = json_decode($req->body, true);
            foreach($decoded as $attr => $val)
            {
                $req->params->__set($attr, $val);
            }
    	}
    	
    	public function postProcess(Request $req, Response $resp, Dispatcher $dispatcher, Service $service, $method)
    	{
    		$resp->body = json_encode($resp->body);
    	}
    }



    /**
     * @Test
     * Disable default output filter AutoFormatFilter as it will try to re-encode this.
     * @OutputFilter
     */
    class AnnotationsTestService extends Service
    {
        public function hello($name)
    	{
    	    $this->response->body = array("msg" => "Hello $name");
    	}
    }
    
Whenever **hello** of service **AnnotationsTest** is called, TestAnnotation's preProcess and postProcess will be invoked and allow to alter every aspect of the requets.

Built-in Annotations
=====================
@Filter, @InputFilter, @OutputFilter
-------------------------------------
There are three different yet simlar filter annotations. Each accept pipe (|) separated list of filters. Such as

    /**
     * @Filter Foo|Bar
     */
     
In this example, before service invokation FooFilter->preProcess and then BarFilter->preProcess methods will be invoked.

After service invokation is complete, first, FooFilter->postProcess then BarFilter->postProcess will be invoked.

You can give any filter name you want. You can derive your own filters from IFilter, OutputFilter or InputFilter classes.

As long as they are in the include path, they'll be loaded automatically.

@InputFilter and @OutputFilter only calls preProcess or postProcess of provided filters respectively.

@Authorize
--------------------------------------
Please [see above](#authorization) for explanation.

Service Class
==============
Service class, the class all the services derive, has two default annotations that you can override

    /**
     * @Authorize    all
     * @OutputFilter AutoFormat
     */
     
[blog1]: http://ea.tl/2012/12/11/design-approaches-to-web-applications-revisited/
