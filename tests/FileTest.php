<?php
require_once('Zita/Core.php');
use Zita\File;

/**
 * User: Engin
 * Date: 10.02.2013
 * Time: 11:41
 */
class FileTest extends PHPUnit_Framework_TestCase
{
    private $file = array (
        'name' => '../99pr^oblems.jpg',
        'type' => 'image/jpeg',
        'tmp_name' => 'C:\\Windows\\Temp\\phpC8E7.tmp',
        'error' => 0,
        'size' => 95969,
    );

    public function testName()
    {
        $f = new File($this->file);
        $this->assertEquals($this->file['name'], $f->getName());
    }

    public function testSize()
    {
        $f = new File($this->file);
        $this->assertEquals($this->file['size'], $f->getSize());
    }

    public function testSafeName()
    {
        $f = new File($this->file);
        // First .. is turned into _, then other characters.
        $this->assertEquals('__99pr_oblems.jpg', $f->getSafeName());
    }

    public function testCopyDst()
    {

    }
}

