<?php
namespace Zita\Filters;

use Zita\Request;
use Zita\Response;

class JsonOutput extends \Zita\OutputPlugin
{	
	public function postProcess(Request $req, Response $resp)
	{
		$resp->body = json_encode($resp->body);
		$resp->headers['Content-type'] = 'application/json';
		$callback = $req->params->callback;
		if($callback != null)
		{
			$resp->body = $callback.'('.$resp->body.');';
		}
	}
}
