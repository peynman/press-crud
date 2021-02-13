<?php

namespace Larapress\CRUD;

/**
 * Interface IUser.
 *
 * @method toArray
 *
 * @property int $id
 * @property string $name
 * @property string $password
 */
interface ICRUDUser
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles();

    /**
     * @param string|string[]|int|int[] $roles
     *
     * @return bool
     */
    public function hasRole($roles);


    /**
     * @return \Larapress\CRUD\Models\Role
     */
    public function getUserHighestRole();

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

    /**
     * @return string[]
     */
    public function getPermissions();
}
