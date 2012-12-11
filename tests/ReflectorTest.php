<?php

require_once('api/0.1/zita/src/Reflector.php');

/**
 * 
 * @author Engin
 * @Foo    bar
 * @Public true
 * @Zor    foo=bar;hell=no
 * @NoParam
 */
class SampleClass
{
	/**
	 * @Public false
	 */
	public function method()
	{
		
	}
}

class ReflectorTest extends PHPUnit_Framework_TestCase
{
	public function testGetClassAnnotation()
	{
		$annotations = Zita\Reflector::getClassAnnotation('SampleClass');
		$expected = array('author' => 'Engin',
						  'Foo' => 'bar',
				          'Public' => true,
				          'Zor' => array('foo' => 'bar', 'hell' => 'no'),
				          'NoParam' => null);
		
		$this->assertEquals(var_export($expected, true), var_export($annotations, true));
	}
	
	public function testGetMethodAnnotation()
	{
		$annotations = Zita\Reflector::getMethodAnnotation('SampleClass', 'method');
		$expected = array('Public' => false);
		$this->assertEquals(var_export($expected, true), var_export($annotations, true));
	}
	
	public function testGetMergedMethodAnnotation()
	{
		$annotations = Zita\Reflector::getMergedMethodAnnotation('SampleClass', 'method');
		$expected = array('author' => 'Engin',
						  'Foo' => 'bar',
				          'Public' => false,
				          'Zor' => array('foo' => 'bar', 'hell' => 'no'),
				          'NoParam' => null);
		$this->assertEquals(var_export($expected, true), var_export($annotations, true));
	}
}