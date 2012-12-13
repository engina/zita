<?php
namespace Zita;

class DB
{
	private static $pdo = null;
	private static $cfg = null;
	
	public static function init($cfg)
	{
		$this->cfg = $cfg;
	}
	
	public static function conn()
	{
		if($this->pdo === null)
		{
			$this->pdo = new PDO($cfg['uri'], $cfg['user'], $cfg['pass']);
		}
		return $pdo;
	}
}

?>