<?php
namespace Zita;

use Zita\ArrayWrapper;

require_once('ArrayWrapper.php');

class Request
{
	public function __construct($request = null)
	{
		if(!$request)
			$request = $_REQUEST;
		$this->params  = new ArrayWrapper($request);
		$this->get     = new ArrayWrapper($_GET);
		$this->post    = new ArrayWrapper($_POST);
		$this->cookie  = new ArrayWrapper($_COOKIE);
		$this->session = new ArrayWrapper(isset($_SESSION) ? $_SESSION : array());
		$this->server  = new ArrayWrapper($_SERVER);
		$this->method  = $this->server->REQUEST_METHOD;

		$headers = array();
		foreach($_SERVER as $key => $value)
		{
			if (substr($key, 0, 5) != 'HTTP_') continue;
			$header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
			$headers[$header] = $value;			
		}
		$this->headers = new ArrayWrapper($headers);
	}
	
	/**
	 * Get parameters as an object
	 * <code>
	 * if($request->get->age < 18)
	 *     throw new Exception();
	 * </code>
	 */
	public $get;
	public $post;
	public $cookie;
	public $session;
	public $params;
	public $server;
	public $method;
	public $headers;
}

?>