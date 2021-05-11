<?php

namespace Larapress\CRUD\Services\RBAC;

interface IPermissionsService
{
    /**
     * Undocumented function
     *
     * @param callable $callback
     * @return void
     */
    public function forEachRegisteredProviderClass($callback);

    /**
     * Undocumented function
     *
     * @return void
     */
    public function updateSuperRole();

    /**
     * Undocumented function
     *
     * @return int
     */
    public function getSuperRolePriority();

    /**
     * Undocumented function
     *
     * @param string $username
     * @param string $password
     * @return mixed
     */
    public function createSuperUser($username, $password);
}
