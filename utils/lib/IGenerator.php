<?php
require_once('lib/Model.php');

interface IGenerator
{
	public function __construct(Model $model, $tpl_dir, $namespace);
	public function generate();
	public function save($path);
}