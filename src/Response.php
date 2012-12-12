<?php
namespace Zita;

class Response
{
	const OK   = 'OK';
	const FAIL = 'FAIL';
	
	public $status;
	public $response;
	public $body;
	public $headers;
	
	public function __construct($body = '', $status = 200, $headers = array())
	{
		$this->body = $body;
		$this->status = $status;
		$this->headers = $headers;
	}
}

?>