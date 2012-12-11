<?php

class Model
{
	public $name;
	public $fields;
	
	public function __construct($name)
	{
		$this->name = $name;
	}
	
	public function getClassName()
	{
		return Inflector::classify($this->name);
	}
	
	public function getPrimaryKey()
	{
		foreach($this->fields as $field)
		{
			if($field->isPrimaryKey())
				return $field;
		}
		echo "Warning: $this->name has no primary key.\n";
		return null;
	}
}

?>
