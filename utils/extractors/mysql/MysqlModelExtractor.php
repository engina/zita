<?php

require_once('MysqlField.php');
require_once('lib/IModelExtractor.php');
require_once('lib/Model.php');

require_once('lib/Inflector.php');

class MysqlModelExtractor implements IModelExtractor
{
	private $models = array();
	
	public function __construct($pdo)
	{
		foreach($pdo->query('SHOW TABLES') as $tbl)
		{
			$model      = new Model($tbl[0]);
			$fields     = array();
			foreach($pdo->query("SHOW FULL COLUMNS FROM `$tbl[0]") as $column)
			{
				array_push($fields, new MysqlField($column));
			}
			$model->fields = $fields;
			array_push($this->models, $model);
		}
	}
	
	public function getModels()
	{
		return $this->models;
	}
}

?>
