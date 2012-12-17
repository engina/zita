<?php
namespace Zita;

/**
 * User: Engin
 * Date: 17.12.2012
 * Time: 05:04
 */
interface ISessionProvider
{
    /**
     * Cleanup dead sessions
     */
    public function cleanup();

    /**
     * @param  int $window Session are valid for this amount of seconds.
     */
    public function setWindow($window);

    /**
     * @return int Validity window of sessions in second.
     */
    public function getWindow();

    /**
     * @param $sid
     * @return Session on success, null on failure
     */
    public function load($sid);

    public function save($sid, $data);

    /**
     * @return Session
     */
    public function create();
}
