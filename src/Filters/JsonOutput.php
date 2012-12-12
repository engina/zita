<?php
namespace Zita\Filters;

require_once('OutputFilter.php');

use Zita\Request;
use Zita\Response;

class JsonOutput extends \Zita\OutputFilter
{
	public function preProcess(Request $req)
	{
		
	}
	
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
