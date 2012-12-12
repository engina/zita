<?php
namespace Zita;
/**
 * Filter base class for filters which are supposed to run after controller executed
 */
abstract class OutputFilter extends Filter
{
	public function preProcess(Request $req)
	{
		
	}
}