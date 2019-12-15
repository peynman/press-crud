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
     * @param bool                      $all
     *
     * @return bool
     */
    public function hasRole($roles, $all = false);

    /**
     * @param string|string[]|int|int[]     $permissions
     * @param bool $all
     *
     * @return bool
     */
    public function hasPermission($permissions, $all = false);

    /**
     * @return void
     */
    public function forgetPermissionsCache();
}
