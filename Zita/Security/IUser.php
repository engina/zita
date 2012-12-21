<?php
namespace Zita\Security;

interface IUser extends \Serializable
{
	public function getIdentifier();
	public function verifyCredentials($data);
	public function getRoles();
    public function hasRole($role);
}