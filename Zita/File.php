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

    /**
     * Moves the file to the destination, if a file with same name already exist,
     * this will will be renamed like myfile0001.jpg.
     *
     * This iterations continues until an available file name is found.
     *
     * Returns the final filename.
     */
    public function moveTo($dst)
    {
        $info = pathinfo($dst);
        $path = $info['dirname'];
        $file = $info['filename'];
        $ext  = $info['extension'];

        $i = 0;
        while(file_exists($dst))
        {
            $f = sprintf("%s%04d.%s", $file, $i++, $ext);
            $dst = Core::path($path, $f);
        }

        error_log('Moving '.$this->file['tmp_name'].' -> '.$dst);
        if(!move_uploaded_file($this->file['tmp_name'], $dst))
        {
            throw new \Exception('File could not be copied');
        }
        return $dst;
    }
}
