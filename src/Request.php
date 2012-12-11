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
	}
	
	public $get;
	public $post;
	public $cookie;
	public $session;
	public $params;
	public $server;
	public $method;
}

?>