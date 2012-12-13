<?php
namespace Zita\Security;

class FacebookAuthenticator implements IAuthenticator
{
	public function authenticate($obj)
	{
		$u = $obj['user'];
	}
}