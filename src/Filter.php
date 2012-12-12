<?php
namespace Zita;

abstract class Filter
{
	private $config;
	
	public function __construct(ArrayWrapper $config = null)
	{
		$this->config = $config;
	}

	abstract public function preProcess(Request $req);
	abstract public function postProcess(Request $req, Response $resp);
}