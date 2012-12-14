<?php
namespace Zita;

abstract class Plugin extends Service
{
	private $config;
	
	public function __construct($config = null)
	{
		$this->config = $config;
	}

	abstract public function preProcess(Request $req);
	abstract public function postProcess(Request $req, Response $resp);
}