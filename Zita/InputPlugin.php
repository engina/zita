<?php
namespace Zita;

/**
 * Filter base class for filters which are supposed to run before controller executed
 */
abstract class InputPlugin extends Plugin
{
	public function postProcess($req, $resp)
	{
	}	
}