<?php
namespace Zita;
/**
 * User: Engin
 * Date: 10.02.2013
 * Time: 11:24
 */
class File
{
    private $file;
    /**
     * new File($_FILES['some_file']);
     */
    public function __construct($arr)
    {
        $this->file = $arr;
    }

    public function getName()
    {
        return $this->file['name'];
    }

    /**
     * Replaces any character which is not a letter, digit or . with _
     */
    public function getSafeName()
    {
        $name = str_replace('..', '_', $this->getName());
        for($i = 0; $i < strlen($name); $i++)
        {
            if(ctype_alpha($name{$i})) continue;
            if(ctype_digit($name{$i})) continue;
            if($name{$i} == '.') continue;
            $name{$i} = '_';
        }
        return $name;
    }

    /**
     * @returns lower case extension of the file, i.e. 'jpg' for foo.Jpg
     */
    public function getExtension()
    {
        $tokens = explode('.', $this->getName());
        return strtolower($tokens[count($tokens)-1]);
    }

    public function getMime()
    {
        return $this->file['type'];
    }

    public function getSize()
    {
        return $this->file['size'];
    }

    public function moveTo($dst)
    {
        if(!move_uploaded_file($this->file['tmp_name'], $dst))
        {
            throw new Exception('File could not be copied');
        }
    }
}
