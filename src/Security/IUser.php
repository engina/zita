<?php
namespace Zita\Security;

interface IUser
{
	public function getIdentifier();
	public function getPassword();
	public function getRoles();
}