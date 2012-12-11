<?php
namespace Zita;

require_once('Request.php');
require_once('Response.php');


class Controller
{
	protected $request;
	
	public function __construct(Request $req)
	{
		$this->request  = $req;
	}
}

?>