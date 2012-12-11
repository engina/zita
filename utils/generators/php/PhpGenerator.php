<?php

require_once('lib/IGenerator.php');
require_once('lib/Template.php');

/**
 * Current implementation generates Models and Controllers for each Model to be used
 * with a PDO driver (probably only gonna work well with MySQL first).
 */
class PhpGenerator implements IGenerator
{
	private $model;
	private $ns;
	private $tpl_dir;
	
	private $result;
	
	public function __construct(Model $model, $tpl_dir, $ns)
	{
		$this->model   = $model;
		$this->tpl_dir = $tpl_dir;
		$this->ns      = $ns;
	}
	
	protected function getTpl($tpl)
	{
		return new Template($this->tpl_dir.DIRECTORY_SEPARATOR.$tpl);
	}
	
	protected function write($str)
	{
		$this->result .= $str;
	}
	
	public function writeHeader()
	{
		$this->write("<?php\n");
		
		$tpl = $this->getTpl('header.txt');
		
		$fields = array();
		foreach($this->model->fields as $field)
		{
			array_push($fields, $field->getName());
		}
		
		$tpl->set(array( 'CLASS'  => Inflector::classify($this->model->getClassName()),
		                 'NS'     => $this->ns,
						 'PK'     => $this->model->getPrimaryKey()->getName(),
						 'TBL'    => $this->model->name,
						 'DATE'   => date(DATE_RFC822),
						 'FIELDS' => $fields));
		$this->write($tpl->process());
	}
	
	public function writeFields()
	{
		foreach($this->model->fields as $field)
		{
			$tpl = $this->getTpl('field_'.strtolower($field->getType()).'.txt');
			$params = array('FIELD'       => $field->getName(),
							'HFIELD'      => ucfirst($field->getName()),
							'ALLOWS_EMPTY'=> $field->allowsEmpty(),
							'MIN'         => $field->getMin(),
							'MAX'         => $field->getMax(),
							'M'           => $field->getM(),
							'D'           => $field->getD(),
							'ENUM_VALUES' => $field->getEnumValues());
			$tpl->set($params);
			$this->write($tpl->process());
		}
	}
	
	public function writeFooter()
	{
		$tpl = $this->getTpl('footer.txt');
		$this->write($tpl->process());
		$this->write("?>\n");
	}
	
	public function generate()
	{
		$this->writeHeader();
		$this->writeFields();
		$this->writeFooter();
	}
	
	public function getBuffer()
	{
		return $this->result;
	}
	
	public function save($path)
	{
		file_put_contents($path.DIRECTORY_SEPARATOR.$this->model->getClassName().'.php', $this->getBuffer());
	}
}

?>
