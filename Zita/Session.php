<?php
namespace Zita;

/**
 * User: Engin
 * Date: 16.12.2012
 * Time: 18:10
 */
class Session extends ArrayWrapper
{
    private $sid;
    private $provider;
    private $data;

    public function __construct($sid, array $data, ISessionProvider $provider)
    {
        parent::__construct($data);
        $this->sid      = $sid;
        $this->provider = $provider;
    }

    function __destruct()
    {
        $this->save();
    }
    /**
     * Persists ISession
     */
    public function save()
    {
        return $this->provider->save($this->getSID(), $this->toArray());
    }

    /**
     * @return mixed Unique session identifier
     */
    public function getSID()
    {
        return $this->sid;
    }
}
