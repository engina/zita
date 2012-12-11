<?php

class MySqlAuthentication implements IAuthentication
{
	private $pdo;
	
	public function __construct($pdo)
	{
		$this->pdo = $pdo;
	}
	
	public function authenticate($obj)
	{
		$u = $obj['user'];
		$p = $obj['pass'];
	}
}