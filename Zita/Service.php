<?php
namespace Zita;

/**
 * @Authorize all
 * @InputFilter AutoFormat
 * @OutputFilter AutoFormat
 */
class Service
{
	protected $request;
	protected $response;
    protected $dispatcher;

	public function __construct(Request $req, Response $resp, Dispatcher $dispatcher)
	{
		$this->request    = $req;
        $this->response   = $resp;
        $this->dispatcher = $dispatcher;
	}
}

?>