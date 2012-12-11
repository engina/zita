<?php

class FacebookAuthentication implements IAuthentication
{
	public function authenticate($obj)
	{
		$u = $obj['user'];
	}
}