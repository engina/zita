<?php
namespace Zita;
/**
 * Filter base class for filters which are supposed to run after controller executed
 */
abstract class OutputPlugin extends Plugin
{
	public function preProcess(Request $req, Response $resp)
	{
	}
}