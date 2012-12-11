<?php
$_T = null;

function str($obj)
{
	return str_replace(array(' ', "\n"), '', var_export($obj, true));
}

function T($key)
{
	global $_T;
	if(is_string($_T[$key]))
		return $_T[$key]."\n";
	return $_T[$key];
}

/**
 * Very basic and native speed templating support.
 *
 * Templates are PHP files with only %VAR% syntactic sugar
 * because <?=$T['VAR']?> is way too ugly.
 */
class Template
{
	private $tpl;
	
	private $params = array();
	
	public function __construct($tpl)
	{
		$this->tpl = $tpl;
	}
	
	/**
	 * @param $key if array, it will be merged with the current params
	 * @param $value if $key is not an array, this will be the value of the key.
	 */
	public function set($key, $value = null)
	{
		if(is_array($key))
		{
			$this->params = array_merge($key, $this->params);
			return;
		}
		$this->params[$key] = $value;
	}
	
	public function process()
	{
		global $_T;
		$_T = $this->params;
		ob_start();
		include $this->tpl;
		$result = ob_get_contents();
		ob_end_clean();
		foreach($this->params as $key => $val)
		{
			if(!is_string($val) && !is_numeric($val)) continue;
			$result = str_replace("%$key%", $val, $result);
		}
		return $result;
	}
}

?>
