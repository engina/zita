<?php
namespace Zita;

class Controller
{
	protected $request;
	
	public function __construct(Request $req)
	{
		$this->request  = $req;
	}
}

?>