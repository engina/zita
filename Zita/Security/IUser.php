<?php
namespace Zita\Security;

interface IUser
{
	public static function getByIdentifier($id);
	public function getIdentifier();
	public function getPassword();
	public function getRoles();
}