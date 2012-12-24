<?php
namespace Zita;

class ArrayWrapper
{
	private $storage = array();
	
	public function __construct(array $arr = array())
	{
		$this->storage = $arr;
	}

    public function toArray()
    {
        return $this->storage;
    }

    public function fromArray(array $arr)
    {
        $this->storage = $arr;
    }

	public function __get($name)
	{
		if(!isset($this->storage[$name]))
		{
			return null;
		}
		return $this->storage[$name];
	}
	
	public function __set($name, $value)
	{
		$this->storage[$name] = $value;
	}

    public function __isset($name)
    {
        return isset($this->storage[$name]);
    }

    public function __unset($name)
    {
        unset($this->storage[$name]);
    }
}

?>