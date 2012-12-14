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
	
	require_once('Zita/Core.php');
	
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
			$this->assertFalse(class_exists('JsonOutput', false));
			$this->assertEquals("\\Zita\\Filters\\JsonOutput", Core::load("Zita\\Filters\\JsonOutput"));
			$this->assertEquals("\\Zita\\Filters\\JsonOutput", Core::load("Zita\\Filters\\JsonOutput"));
		}
		
		/**
		 * @expectedException Zita\ClassNotFoundException
		 */
		public function testAutoLoadFail()
		{
			Core::load("Zita\\FooBarBaz31337");
		}
		
		public function testPath()
		{
			$paths = array('foo', 'bar', 'baz');
			$expected = join(DIRECTORY_SEPARATOR, $paths);
			$this->assertEquals($expected, Core::path('foo', 'bar', 'baz'));
		}
		
		public function testIncludePath()
		{
			$path = get_include_path();
			Core::addIncludePath('test');
			$this->assertEquals('test'.PATH_SEPARATOR.$path, get_include_path());
		}

		private $dummyPath = 'test/Add/Service/path';
		
		public function testAddServicePath()
		{
			$this->assertFalse(Core::removeServicePath($this->dummyPath));
			$this->assertFalse(array_search($this->dummyPath, Core::getServicePaths()));
			Core::addServicePath($this->dummyPath);
			$this->assertEquals(0, array_search($this->dummyPath, Core::getServicePaths()));
		}
		
		/**
		 * @depends testAddServicePath
		 */
		public function testRemoveServicePath()
		{
			$this->assertEquals(0, array_search($this->dummyPath, Core::getServicePaths()));
			Core::removeServicePath($this->dummyPath);
			$this->assertFalse(array_search($this->dummyPath, Core::getServicePaths()));
		}

        public function testErrors()
        {
            $this->assertCount(0, Core::getErrors());
            Core::logError(new Exception("Test"), 100);
            $this->assertCount(1, Core::getErrors());
            $errors = Core::getErrors();
            $this->assertEquals('CoreTest', $errors[0]['source']);
            $this->assertEquals('testErrors', $errors[0]['method']);
            $this->assertEquals(123, $errors[0]['line']);
            $this->assertEquals('\Exception', $errors[0]['type']);
            $this->assertEquals('Test', $errors[0]['msg']);
            $this->assertEquals(100, $errors[0]['code']);
        }
	}
}