<?php
namespace Zita\Annotations;

require_once('IAnnotation.php');

use Zita\IAnnotation;
use Zita\Request;
use Zita\Response;
use Zita\Controller;
use Zita\Core;

/**
 * This class handles @Filter annotations.
 * Basically @Filter annotation expects a comma separated list of class names
 * which are derived from Filter class.
 *
 */
class FilterAnnotation implements IAnnotation
{
	private $cfg;
	private $filters;
	public function __construct($cfg)
	{
		$this->cfg = $cfg;
		$this->filters = explode(',', $cfg);
	}
	
	public function preProcess(Request $req, Response $resp, Controller $controller = null, $method = null)
	{
		foreach($this->filters as $filter)
		{
			$filterClass = Core::load($filter);
			if($filterClass === false)
				throw new \Zita\Exception("Unknown filter class '$filter'");
			$filter = new $filterClass();
			if(!($filter instanceof \Zita\Filter))
				throw new Exception("Filter class does not implement abstract Filter class methods");
			$filter->preProcess($req);
		}
	}
	
	public function postProcess(Request $req, Response $resp, Controller $controller = null, $method = null)
	{
		foreach($this->filters as $filter)
		{
			$filterClass = Core::load($filter);
			if($filterClass === false)
				throw new \Zita\Exception("Unknown filter class '$filter'");
			$filter = new $filterClass();
			if(!($filter instanceof \Zita\Filter))
				throw new Exception("Filter class does not implement abstract Filter class methods");
			$filter->postProcess($req, $resp);
		}
	}
}