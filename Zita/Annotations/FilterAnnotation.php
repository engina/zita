<?php
namespace Zita\Annotations;

use Zita\IAnnotation;
use Zita\Request;
use Zita\Response;
use Zita\Service;
use Zita\Dispatcher;
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

    protected function getFilters()
    {
        $filters = array();
        foreach($this->filters as $filter)
        {
            $filter .= 'Filter';
            $filterClass = Core::load($filter);
            if($filterClass === false)
                throw new \Zita\Exception("Unknown filter class '$filter'");
            $filter = new $filterClass(null);
            if(!($filter instanceof \Zita\IFilter))
                throw new Exception("Filter class does not implement abstract Filter class methods");
            $filters[] = $filter;
        }
        return $filters;
    }

	public function preProcess(Request $req, Response $resp, Dispatcher $dispatcher, Service $service, $method)
	{
		foreach($this->getFilters() as $filter)
		{
			$filter->preProcess($req, $resp, $dispatcher, $service, $method);
		}
	}
	
	public function postProcess(Request $req, Response $resp, Dispatcher $dispatcher, Service $service, $method)
	{
		foreach($this->getFilters() as $filter)
		{
			$filter->postProcess($req, $resp, $dispatcher, $service, $method);
		}
	}
}