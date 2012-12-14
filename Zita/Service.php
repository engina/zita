<?php
namespace Zita;

class Service
{
	protected $request;
	
	public function __construct(Request $req)
	{
		$this->request  = $req;
	}
}

?>