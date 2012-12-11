Zita
=====
Zita is my take on how a web service framework should be.

Getting Started
-----------------
api.php

    <?php
    require('zita/src/Dispatcher.php');
    Zita\Dispatcher::dispatch();
    ?>

Controllers/MyController.php

    <?php
    namespace Controllers;
    
    class MyController extends \Zita\Controller
    {
        public function hello($name, $surname)
    	{
    		return new \Zita\Response("Hello Ms/Mr $surname, or shall I simply call you $name ?");
    	}
    }
    ?>
Now, you can call api.php with either GET or POST methods with c=MyController and m=hello. 

**Request**

    GET http://localhost/api.php?c=MyController&m=hello

**Response**

    array (
      'errno' => 0,
      'msg' => 'Missing parameters',
    )

**Request**

    GET http://localhost/api.php?c=MyController&m=hello&name=John&surname=Doe

**Response**

    Hello Ms/Mr Doe, or shall I simply call you John ?

**Note:** Both GET and POST methods will work.

Furthermore you can discover the API.

**Request**

    GET http://localhost/api.php?DISCOVER
    
**Response**

    array (
      'MyController' => 
      array (
        'hello' => 
        array (
          'parameters' => 
          array (
            'name' => 
            array (
              'optional' => false,
              'type' => 'String',
            ),
            'surname' => 
            array (
              'optional' => false,
              'type' => 'String',
            ),
          ),
        ),
      ),
    )
    
