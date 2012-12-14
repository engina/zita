<?php
namespace Zita;

class ZitaException extends \Exception
{
	public function __construct($base, $msg, $code, $previous)
	{
		parent::__construct($msg, $code + $base, $previous);
	}
}

?>
