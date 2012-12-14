<?php
namespace Zita;

class PluginContainer
{
	private $callbacks = array();
	
	public function add(Plugin $c)
	{
		$this->callbacks[] = $c;
		return $c;
	}
	
	public function remove(Plugin $c)
	{
		$key = array_search($c, $this->callbacks);
		if($key === false)
			throw new PluginException('Event handler not found', 2);
		unset($this->callbacks[$key]);
	}

    /**
     * @param Request $req
     * @param Response $resp
     * @return true if service should not be run
     */
    public function preProcess(Request $req, Response $resp)
	{
		foreach($this->callbacks as $callback)
		{
            try
            {
			    $callback->preProcess($req, $resp);
            }
            catch(PluginStopException $e)
            {
                return false;
            }
            catch(PluginCancelException $e)
            {
                return true;
            }
		}
	}
	
	public function postProcess(Request $req, Response $resp)
	{
		foreach($this->callbacks as $callback)
		{
            try
            {
                $callback->postProcess($req, $resp);
            }
            catch(PluginStopException $e)
            {
                break;
            }
		}
	}
}