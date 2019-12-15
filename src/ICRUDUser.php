<?php

namespace Larapress\CRUD;

/**
 * Interface IUser.
 *
 * @property int $id
 * @property string $name
 * @property string $password
 */
interface ICRUDUser
{
    /**
     * @param string|string[]|int|int[] $roles
     *
     * @return bool
     */
    public function hasRole($roles);

    /**
     * @param string|string[]|int|int[]     $permissions
     *
     * @return bool
     */
    public function hasPermission($permissions);

    /**
     * @return void
     */
    public function forgetPermissionsCache();
}
