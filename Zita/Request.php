<?php
namespace Zita;

use Zita\ArrayWrapper;

class Request
{
	public function __construct()
	{
		// $_REQUEST aggragates $_GET, $_POST and $_COOKIE
		// Though which one overrides the other is decided by variables_order directive
		// of php.ini, therefore we aggregate $_COOKIE, $_POST, $_GET in this specific order.
		// Meaning $_COOKIE has the least priority where $_GET has the most.
		$params        = $_COOKIE;
		$params        = array_merge($params, $_POST);
		$params        = array_merge($params, $_GET);
        foreach($_FILES as $name => $file)
        {
            $params[$name] = new File($file);
        }
		$this->params  = new ArrayWrapper($params);
		$this->get     = new ArrayWrapper($_GET);
		$this->post    = new ArrayWrapper($_POST);
		$this->cookie  = new ArrayWrapper($_COOKIE); 
		$this->session = null;
		$this->server  = new ArrayWrapper($_SERVER);
		$this->method  = $this->server->REQUEST_METHOD;
        $this->body    = file_get_contents('php://input');
		$headers = array();
		foreach($_SERVER as $key => $value)
		{
			if (substr($key, 0, 5) != 'HTTP_') continue;
			$header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
			$headers[$header] = $value;			
		}
        // Normalize idiotic PHP.
        if(isset($_SERVER['CONTENT_TYPE']))
        {
            $headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];
        }
		$this->headers = $headers;
	}
	
	/**
	 * Get parameters as an object
	 * <code>
	 * if($request->get->age < 18)
	 *     throw new Exception();
	 * </code>
	 */
	public $get     = array();
	public $post    = array();
	public $cookie  = array();
	public $session = array();
	public $params  = array();
	public $server  = array();
	public $method  = array();
	public $headers = array();
    public $handled = false;
    public $body    = '';
	
	/**
	 * Authenticated IUser.
	 * 
	 * null if not authenticated.
	 */
	public $user    = null;
}

?>