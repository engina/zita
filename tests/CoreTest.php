<?php
namespace TestNS1
{
	class A
	{
		
	}
	
	class B
	{
		
	}
}

namespace TestNS1\SubA
{
	class D
	{
			
	}
}

namespace TestNS2
{
	class A
	{
		
	}
	
	class B
	{
		
	}
	
	class C
	{
		
	}
}

namespace TestNS2\SubB
{
	class D
	{
			
	}
}

namespace // Global
{
	class A
	{
		
	}
	
	require_once('api/0.1/zita/Zita/Core.php');
	
	use Zita\Core;
	
	class CoreTest extends PHPUnit_Framework_TestCase
	{
		public function testAutoLoading()
		{
			$this->assertEquals("C:\\Users\\Engin\\Code\\PHP\\WMI\\api\\0.1\\zita", ZITA_ROOT);
			$this->assertEquals("\\A", Core::load("A"));
			$this->assertEquals("\\TestNS1\\A", Core::load("TestNS1\\A"));
			$this->assertEquals("\\TestNS2\\A", Core::load("TestNS2\\A"));
			$this->assertEquals("\\TestNS1\\B", Core::load("B"));
			$this->assertEquals("\\TestNS1\\SubA\D", Core::load("D"));
			$this->assertEquals("\\TestNS1\\SubA\D", Core::load("SubA\\D"));
			$this->assertEquals("\\TestNS2\\SubB\D", Core::load("SubB\\D"));
			$this->assertEquals("\\TestNS2\\C", Core::load("C"));
			$this->assertFalse(class_exists('JsonOutput'));
			$this->assertEquals("\\Zita\\Filters\\JsonOutput", Core::load("Zita\\Filters\\JsonOutput"));
			$this->assertEquals("\\Zita\\Filters\\JsonOutput", Core::load("Zita\\Filters\\JsonOutput"));
			
		}
	}
}