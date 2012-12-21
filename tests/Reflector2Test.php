<?php
namespace R2;
require_once('Zita/Core.php');

/**
 * 
 * @Secure
 * @OutputFilter AutoFormat
 */
class ParentClass
{
	/**
     * @OutputFilter AutoFormat|Prettify
     * @InputFilter  MyDecoder
     * @MyAnnotation MyImplementation
	 */
	public function method()
	{
		
	}
}

/**
 * @OutputFilter Json|Minify
 * @Cache
 */
class ChildClass extends ParentClass
{
    /**
     * @MyAnnotation    OtherImplementation
     * @OtherAnnotation Foo
     */
    public function method()
    {

    }
}

class Reflector2Test extends \PHPUnit_Framework_TestCase
{
	public function testGetClassAnnotation()
	{
		$annotations = \Zita\Reflector::getClassAnnotation('R2\ParentClass');
		$expected = array('Secure'       => null,
                          'OutputFilter' => 'AutoFormat');
        $this->assertEquals(var_export($expected, true), var_export($annotations, true));
	}

	public function testGetMergedMethodAnnotation()
	{
		$annotations = \Zita\Reflector::getMethodAnnotation('R2\Parentclass', 'method');
		$expected = array('Secure'       => null,
                          'OutputFilter' => 'AutoFormat|Prettify',
                          'InputFilter'  => 'MyDecoder',
                          'MyAnnotation' => 'MyImplementation');
        $this->assertEquals(var_export($expected, true), var_export($annotations, true));
	}

    public function testAnnotationInheritance()
    {
        $annotations = \Zita\Reflector::getClassAnnotation('R2\ChildClass');
        $expected = array('Secure'       => null,
                          'OutputFilter' => 'Json|Minify',
                          'Cache'        => null);
        $this->assertEquals(var_export($expected, true), var_export($annotations, true));
    }

    public function testAnnotationMethodInheritance()
    {
        $annotations = \Zita\Reflector::getMethodAnnotation('R2\ChildClass', 'method');
        $expected = array('Secure'       => null,
                          'OutputFilter' => 'Json|Minify',
                          'InputFilter'  => 'MyDecoder',
                          'MyAnnotation' => 'OtherImplementation',
                          'Cache'        => null,
                          'OtherAnnotation' => 'Foo');
        $this->assertEquals(var_export($expected, true), var_export($annotations, true));
    }
}