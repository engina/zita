<?php
namespace Zita;

class Service
{
	protected $request;
	protected $response;
	public function __construct(Request $req, Response $resp)
	{
		$this->request  = $req;
        $this->response = $resp;
	}
}

?>