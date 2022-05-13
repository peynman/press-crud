<?php

namespace Larapress\CRUD;
use \Larapress\CRUD\Models\Role;

/**
 * An Interface for Role-Based users
 *
 * @property int        $id
 * @property string     $name
 * @property string     $password
 */
interface ICRUDUser
{
    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany;

    /**
     * @param string|string[]|int|int[] $roles
     *
     * @return bool
     */
    public function hasRole($roles);

    /**
     * @return Role|null
     */
    public function getUserHighestRole(): Role|null;

    /**
     * @param string|string[]|int|int[] $permissions
     *
     * @return bool
     */
    public function hasPermission($permissions): bool;

    /**
     * @return string[]
     */
    public function getPermissions();

    /**
     * @return void
     */
    public function forgetPermissionsCache();
}
