<?php
namespace Zita\Encoders;

interface IEncoder
{
	public function __construct(\Zita\Request $req, \Zita\Response $resp);
	public function encode();
}