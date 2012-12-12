<?php
namespace Zita\Security;

require_once('IAuthenticator.php');

class FacebookAuthenticator implements IAuthenticator
{
	public function authenticate($obj)
	{
		$u = $obj['user'];
	}
}