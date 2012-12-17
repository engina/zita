<?php
require_once('Zita/Core.php');

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
     * If any annotation is new, such as AnnoA here, as it is not overriding anything
     * from class Annotations, the new annotation will be appended back to all currently
     * existing annotations.
     * @AnnoA  foo
	 * @Public false
	 */
	public function method()
	{
		
	}
}

/**
 * This should inherit all annotations from SampleClass and only override Foo
 * @Foo baz
 * @AnnoC duh
 */
class ChildClass extends SampleClass
{
    /**
     * This should inherit all mergedAnnotations of SampleClass::method and override AnnoA
     * @AnnoA bar
     */
    public function method()
    {

    }
}

class ChildClass2 extends ChildClass
{

}

/**
 * @Foo l0lz
 */
class ChildClass3 extends ChildClass2
{
    /**
     * @Public true
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
				          'Public' => 'true',
				          'Zor' => 'foo=bar;hell=no',
				          'NoParam' => null);
		
		$this->assertEquals(var_export($expected, true), var_export($annotations, true));
	}

	public function testGetMergedMethodAnnotation()
	{
		$annotations = Zita\Reflector::getMethodAnnotation('SampleClass', 'method');
		$expected = array('author' => 'Engin',
						  'Foo' => 'bar',
				          'Public' => 'false',
				          'Zor' => 'foo=bar;hell=no',
				          'NoParam' => null,
                          'AnnoA' => 'foo');
		$this->assertEquals(var_export($expected, true), var_export($annotations, true));
	}

    public function testAnnotationInheritance()
    {
        $annotations = Zita\Reflector::getClassAnnotation('ChildClass');
        $expected = array('author' => 'Engin',
            'Foo' => 'baz',
            'Public' => 'true',
            'Zor' => 'foo=bar;hell=no',
            'NoParam' => null,
            'AnnoC' => 'duh');
        $this->assertEquals($expected, $annotations);
    }

    public function testAnnotationMethodInheritance()
    {
        $annotations = Zita\Reflector::getClassAnnotation('ChildClass');
        $expected = array('author' => 'Engin',
            'Foo' => 'baz',
            'Public' => 'true',
            'Zor' => 'foo=bar;hell=no',
            'NoParam' => null,
            'AnnoC' => 'duh');
        $this->assertEquals($expected, $annotations);
    }

    public function testDeepAnnotationInheritance()
    {
        $annotations = Zita\Reflector::getClassAnnotation('ChildClass3');
        $expected = array('author' => 'Engin',
            'Foo' => 'l0lz',
            'Public' => 'true',
            'Zor' => 'foo=bar;hell=no',
            'NoParam' => null,
            'AnnoC' => 'duh');
        $this->assertEquals($expected, $annotations);
    }

    public function testNoInherit()
    {
        $annotations = Zita\Reflector::getClassAnnotation('ChildClass3', false);
        $expected = array('Foo' => 'l0lz');
        $this->assertEquals($expected, $annotations);
    }

    public function testInheritMethod()
    {
        $annotations = Zita\Reflector::getMethodAnnotation('ChildClass3', 'method');
        $expected = array(
            'author' => 'Engin',
            'Foo'    => 'l0lz',
            'Public' => 'true',
            'Zor'    => 'foo=bar;hell=no',
            'NoParam' => null,
            'AnnoA'  => 'bar',
            'AnnoC'  => 'duh');
        $this->assertEquals($expected, $annotations);
    }
    public function testNoInheritMethod()
    {
        $annotations = Zita\Reflector::getMethodAnnotation('ChildClass3', 'method', false);
        $expected = array('Foo' => 'l0lz', 'Public' => 'true');
        $this->assertEquals($expected, $annotations);
    }
}