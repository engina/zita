<?php
namespace Zita\Security;

require_once('IAuthenticator.php');

class PdoAuthenticator implements IAuthenticator
{
	private $identifierField;
	private $passwordField;
	private $selectStatement;
	private $rolesSelectStatement;
	
	public function __construct(\PDO $pdo = null, $tbl = 'users', $identifier_field = 'username', $password_field = 'password')
	{
		$this->identifierField = $identifier_field;
		$this->passwordField   = $password_field;
		$this->selectStatement = $this->rolesSelectStatement = null;
		if($pdo == null)
			return;

		$this->selectStatement = $pdo->prepare("SELECT * FROM `$tbl` WHERE `$identifier_field` = :identifier");
	}
	
	public function setSelectStatement(\PDOStatement $select)
	{
		$this->selectStatement = $select;
	}
	
	public function setRolesStatement(\PDOStatement $roles_select)
	{
		$this->rolesSelectStatement = $roles_select;
	}
	
	public function authenticate($obj)
	{
		$i = $obj['identifier'];
		$p = $obj['password'];
		$r = $this->selectStatement->bindParam(':identifier', $i) | $this->selectStatement->bindParam(':identifier', $i);
		if(!$r)
		{
			throw new \Exception('Could not bind parameters. Please check your statement.');
		}
		$this->selectStatement->execute();
		if($this->selectStatement->rowCount() == 0)
			throw new \Exception('Authentication failed.');
		$result = $this->selectStatement->fetch(\PDO::FETCH_ASSOC);
		$password = $result[$this->passwordField];
		list($algo, $hashed) = explode(':', $password);
		if(hash($algo, $p) != $hashed)
			throw new \Exception('Authentication failed.');
	}
}