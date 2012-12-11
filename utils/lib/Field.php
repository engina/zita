<?php

abstract class Field
{
	const TYPE_INT        = 'int';
	const TYPE_TEXT       = 'text';
	const TYPE_FLOAT      = 'float';
	const TYPE_DATETIME   = 'datetime';
	const TYPE_ENUM       = 'enum';
	const TYPE_REFERENCE  = 'ref';
	
	abstract public function __construct($column);
	
	abstract function isPrimaryKey();
	abstract function isAutoIncrement();
	abstract function getName();
	abstract function getType();
	abstract function getMin();
	abstract function getMax();
	abstract function getM();
	abstract function getD();
	abstract function allowsEmpty();
	abstract function getEnumValues();
}

?>
