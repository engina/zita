<?php
namespace Zita;

/**
 * File based session provider
 *
 * Could have used PHP Sessions though it'd have require us to modify application wide session configuration
 * which might break already application.
 */
class FileSessionProvider implements  ISessionProvider
{
    private $path   = '';
    private $window = 600;

    public function __construct($path, $window = 600)
    {
        $this->setPath($path);
        $this->setWindow($window);
    }

    public function __destruct()
    {
        $this->cleanup();
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function getPath()
    {
        return $this->path;
    }

    /**
     * Cleanup dead dead sessions
     */
    public function cleanup()
    {
        foreach(glob(Core::path($this->getPath(), '*')) as $file)
        {
            $atime = fileatime($file);
            $time  = time();
            if($atime < ($time - self::getWindow()))
                unlink($file);
        }
        return true;
    }

    /**
     * @param  int $window Session are valid for this amount of seconds.
     */
    public function setWindow($window)
    {
        $this->window = $window;
    }

    /**
     * @return int Validity window of sessions in second.
     */
    public function getWindow()
    {
        return $this->window;
    }

    /**
     * @param $sid
     * @return Session
     */
    public function load($sid)
    {
        $data = file_get_contents(Core::path($this->getPath(), $sid));
        if($data === false)
            return null;
        $data = unserialize($data);
        if($data === NULL)
            return null;
        return new Session($sid, $data, $this);
    }

    /**
     * @return Session
     */
    public function create()
    {
        $sid = md5(rand());
        if(file_put_contents(Core::path($this->getPath(), $sid), serialize(array())) === false)
            return null;
        return self::load($sid);
    }

    /**
     * Persists ISession
     */
    public function save($sid, $data)
    {
        return file_put_contents(Core::path($this->getPath(), $sid), serialize($data)) !== false;
    }
}
