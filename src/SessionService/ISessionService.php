<?php

namespace Larapress\CRUD\SessionService;

interface ISessionService
{
    /**
     * @param string    $key
     * @param string    $value
     * @param \Larapress\CRUD\ICRUDUser $user
     *
     * @return ISessionService
     */
    public function setForUser($key, $value, $user = null);

    /**
     * @param string    $key
     * @param \Larapress\CRUD\ICRUDUser $user
     * @param mixed     $default
     *
     * @return mixed
     */
    public function getForUser($key, $user = null, $default = null);
}
