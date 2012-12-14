<?php
namespace Zita;

const CORE_EXCEPTION_BASE = 1000;
class CoreException extends ZitaException
{
	public function __construct($msg, $code = 0, $previous = null)
	{
		parent::__construct(CORE_EXCEPTION_BASE, $msg, $code, $previous);
	}
}