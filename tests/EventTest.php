<?php
require_once('api/0.1/zita/src/Event.php');
use \Zita\Event;

class DummyClass
{
	public $str;
}

function a(DummyClass $c)
{
	$c->str .= 'a';
}

function b(DummyClass $c)
{
	$c->str .= 'b';
}

function c(DummyClass $c)
{
	$c->str .= 'c';
}

function canceller(DummyClass $c)
{
	return false;
}

class EventTest extends PHPUnit_Framework_TestCase
{
	public function testAdd()
	{
		$e = new Event();
		$e->add('a');
		$dummy = new DummyClass();
		$e->fire($dummy);
		$this->assertEquals('a', $dummy->str);
	}
	
	public function testMultiAdd()
	{
		$e = new Event();
		$e->add('a');
		$e->add('b');
		$e->add('c');
		$dummy = new DummyClass();
		$e->fire($dummy);
		$this->assertEquals('abc', $dummy->str);
	}
	
	public function testRemove()
	{
		$e = new Event();
		$e->add('a');
		$e->add('b');
		$e->add('c');
		$e->remove('b');
		$dummy = new DummyClass();
		$e->fire($dummy);
		$this->assertEquals('ac', $dummy->str);
	}
	
	public function testCanceller()
	{

		$e = new Event();
		$e->add('a');
		$e->add('b');
		$e->add('canceller');
		$e->add('c');
		$dummy = new DummyClass();
		$e->fire($dummy);
		$this->assertEquals('ab', $dummy->str);
	}
}