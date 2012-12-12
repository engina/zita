<?php
namespace Zita;

class FacebookAuthentication implements IAuthentication
{
	public function authenticate($obj)
	{
		$u = $obj['user'];
	}
}