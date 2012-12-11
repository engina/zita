<?php

require_once('lib/Field.php');

class MysqlField extends Field
{
	private $field;
	private $allows_empty;
	private $min;
	private $max = PHP_INT_MAX;
	private $M   = 65;
	private $D   = 30;
	private $enum_values = 'array()';
	
	private $raw_data;
	private $type;
	public function __construct($field)
	{
		$this->raw_data = $field;
		$type = $field['Type'];
		$p = strpos($type, '(');
		$this->type = $type = substr($type, 0, $p === false ? 64 : $p);
		$this->field = $field['Field'];
		$this->allows_empty = $this->_allowsEmpty($field) ? 'true' : 'false';
		$this->min = ~PHP_INT_MAX;;
		$method = 'build'.ucfirst($type).'Field';
		$this->$method($field);
	}
	
	protected function _allowsEmpty($field)
	{
		if($field['Null'] == 'YES') return true;
		if($field['Default'] != 'NULL') return true;
		return false;
	}
	
	public function isPrimaryKey()
	{
		return $this->raw_data['Key'] == 'PRI';
	}
	
	public function isAutoIncrement()
	{
		return $this->raw_data['Extra'] == 'auto_increment';
	}
	
	public function getName()
	{
		return $this->field;
	}
	
	public function getType()
	{
		return $this->type;
	}

	function allowsEmpty()
	{
		return $this->allows_empty;
	}
	
	function getMin()
	{
		return $this->min;
	}
	function getMax()
	{
		return $this->max;
	}
	function getM()
	{
		return $this->M;
	}
	function getD()
	{
		return $this->D;
	}
	
	function getEnumValues()
	{
		return $this->enum_values;
	}
	
	protected function buildVarcharField($field)
	{
		$this->max = substr($field['Type'], 8, -1);
	}
	
	protected function buildIntField($field)
	{
		$this->max = substr($field['Type'], 4, -1);
	}
	
	protected function buildDecimalField($field)
	{
		$precision = substr($field['Type'], 8, -1);
		list($M, $D) = explode(',', $precision);
		$this->M = $M;
		$this->D = $D;
	}
	
	protected function buildDatetimeField($field)
	{
	}
	
	protected function buildTimestampField($field)
	{
	}
	
	protected function buildTextField($field)
	{
		$this->max = 65535;
	}
	
	protected function buildEnumField($field)
	{
		$arr = explode(',', substr($field['Type'], 5, strlen($field['Type'])-6));
		foreach($arr as $key => $value)
			$arr[$key] = substr($value, 1, strlen($value) - 2);
		$this->enum_values = $arr;
	}
}

?>