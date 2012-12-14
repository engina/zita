<?php
namespace Zita\Annotations;

use Zita\IAnnotation;
use Zita\Request;
use Zita\Response;
use Zita\Service;
use Zita\Core;


class FilterAnnotation implements IAnnotation
{
	private $cfg;
	private $filters;
	public function __construct($cfg)
	{
		$this->cfg = $cfg;
		$this->filters = explode('|', $cfg);
	}
	
	public function preProcess(Request $req, Response $resp, Service $service = null, $method = null)
	{
		foreach($this->filters as $filter)
		{
			$filterClass = Core::load($filter);
			if($filterClass === false)
				throw new \Zita\Exception("Unknown filter class '$filter'");
			$filter = new $filterClass();
			if(!($filter instanceof \Zita\Plugin))
				throw new Exception("Filter class does not implement abstract Filter class methods");
			$filter->preProcess($req, $resp);
		}
	}
	
	public function postProcess(Request $req, Response $resp, Service $service = null, $method = null)
	{
		foreach($this->filters as $filter)
		{
			$filterClass = Core::load($filter);
			if($filterClass === false)
				throw new \Zita\Exception("Unknown filter class '$filter'");
			$filter = new $filterClass();
			if(!($filter instanceof \Zita\Plugin))
				throw new \Zita\Exception("Filter class does not implement abstract Plugin class methods");
			$filter->postProcess($req, $resp);
		}
	}
}