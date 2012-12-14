<?php
namespace Zita;

const DISPATCHER_EXCEPTION_BASE = 2000;

class DispatcherException extends ZitaException
{
	public function __construct($msg, $code = 0, $previous = null)
	{
		parent::__construct(DISPATCHER_EXCEPTION_BASE, $msg, $code, $previous);
	}
}