<?php
namespace Zita\Encoders;

require_once('IEncoder.php');

class Json implements IEncoder
{
	private $req;
	private $resp;
	
	public function __construct(\Zita\Request $req, \Zita\Response $resp)
	{
		$this->req  = $req;
		$this->resp = $resp;
	}
	
	public function encode()
	{
		$this->resp->body = json_encode($this->resp->body);
		$this->resp->headers['Content-type'] = 'application/json';
		$callback = $this->req->params->callback;
		if($callback != null)
		{
			$this->resp->body = $callback.'('.$this->resp->body.');';
		}
	}
}