<?php
namespace Zita\Security;

interface IUser extends \Serializable
{
	public static function getByIdentifier($id);
	public function getIdentifier();
	public function getPassword();
	public function getRoles();
}