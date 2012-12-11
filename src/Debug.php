<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 'off');
ini_set('error_log', E_LOG);
ini_set('error_append_string', "\n");

$ERR_500 = false;

function errorHandler($errno , $errstr, $errfile, $errline, $errcontext)
{
	global $IS_API_CALL;
	
	$ctx_id = date('z-Gi');
	$str = $errstr.' ['. $errno . '] in ' . $errfile . ' on line ' . $errline .
			   "\n Context: $ctx_id (check context.log for details)\n";
	
	$ctx = '================ CTX ID '.$ctx_id.' ('.date('r').') ================';
	foreach($errcontext as $key => $val)
	{
		$ctx .= '    '.$key.' = ';
		if(is_string($val))
			$ctx .= $val;
		else
			$ctx .= var_export($val, true);
		$ctx .= "\n";
	}
	$ctx .= '################ CTX ID '.$ctx_id.' ('.date('r').') ################';
	file_put_contents(E_LOG . '.context', $ctx, FILE_APPEND);
	$str .= "\n Backtrace: \n";
	$bt = debug_backtrace();
	array_shift($bt); // errorHandler call itself
	$total = count($bt);
	foreach($bt as $b)
	{
		$str .= '    '.$total--.'. ';
		if(isset($b['file']))
			$str .= $b['file'];
		if(isset($b['line']))
			$str .= ':'.$b['line'].' ';
		if(isset($b['class']))
			$str .= $b['class'].$b['type'];
		if(isset($b['function']))
			$str .= $b['function'];
		if(isset($b['args']))
		{
			$str .= '(';
			foreach($b['args'] as $a)
			{
				$t = gettype($a);
				switch($t)
				{
					case 'boolean':
					case 'integer':
					case 'double':
					case 'string':
						$str .= $a.', ';
						break;
					case 'array':
						$str .=  preg_replace('/[\r\n]+/', '', var_export($a, true)).', ';
						break;
					case 'object':
						$str .= get_class($a).', ';
						break;
					case 'resource':
					case 'NULL':
					case 'unknown type':
					$str .= $t.', ';
				}
			}
			$str = substr($str, 0, strlen($str)-2);
			$str .= ')';
		}	
		$str .= "\n";
	}
	error_log($str);
	return;
}

set_error_handler('errorHandler');
// let PHP handle these, as they are error_loged automatically, it is already good.

// This means, we do not allow even NOTICEs. Code must be perfect to pass.
// Any flaw in the code will result in a redirect to 500.php and will be logged.

function dump($mixed)
{
	error_log(var_export($mixed, true));
}	

?>
