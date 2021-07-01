<?php

namespace Larapress\CRUD;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;
use Larapress\CRUD\Extend\Helpers;
use Larapress\CRUD\Factories\UserFactory;
use Larapress\CRUD\Models\Permission;
use Larapress\CRUD\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

trait BaseCRUDUser
{
    use HasFactory;

    /**
     * Undocumented function
     *
     * @return Factory
     */
    protected static function newFactory()
    {
        return UserFactory::new();
    }

    /** @var array */
    public $cachedRoles = null;
    /** @var array */
    public $cachedPermissions = null;
    /** @var Role */
    public $cachedHighestRole = null;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'user_role',
            'user_id',
            'role_id'
        );
    }

    /**
     * Check if user has permission or not.
     *
     * @param string|int|Permission|string[]|int[]|Permission[] $permissions
     *
     * @return bool
     */
    public function hasPermission($permissions)
    {
        if (is_array($permissions)) {
            foreach ($permissions as $perm) {
                if ($this->checkPermission($perm)) {
                    return true;
                }
            }
        }

        return $this->checkPermission($permissions);
    }

    /**
     * @param string|string[]|int|int[] $roles
     *
     * @return bool
     */
    public function hasRole($roles)
    {
        if (is_array($roles)) {
            foreach ($roles as $role) {
                if ($this->checkRole($role)) {
                    return true;
                }
            }
        }

        return $this->checkRole($roles);
    }

    /**
     * @param string|int|Permission $permission
     *
     * @return bool
     */
    protected function checkPermission($permission)
    {
        if (is_null($this->cachedRoles)) {
            $this->getPermissions();
        }

        if (is_object($permission)) {
            foreach ($this->cachedPermissions as $my_permission) {
                if ($my_permission[0] === $permission->id) {
                    return true;
                }
            }
        } else {
            $index_to_check = 1; // permission name
            if (is_numeric($permission)) {
                $index_to_check = 0; // permission id
            }
            foreach ($this->cachedPermissions as $my_permission) {
                if ($my_permission[$index_to_check] === $permission) {
                    return true;
                }
            }
        }

        return false;
    }


    /**
     * @return \Larapress\CRUD\Models\Role
     */
    public function getUserHighestRole()
    {
        return Helpers::getCachedValue(
            'larapress.users.'.$this->id.'.roles.highest',
            ['user.permissions:'.$this->id],
            86400,
            true,
            function () {
                return $this->roles()->orderBy('priority', 'DESC')->first();
            },
        );
    }

    /**
     * @param string|int|Role $role
     *
     * @return bool
     */
    protected function checkRole($role)
    {
        if (is_null($this->cachedRoles)) {
            $this->getPermissions();
        }

        if (is_object($role)) {
            foreach ($this->cachedRoles as $r) {
                if ($role->id == $r[0]) {
                    return true;
                }
            }
        } else {
            $index_to_check = 'name';
            if (is_numeric($role)) {
                $index_to_check = 'id';
            }
            foreach ($this->cachedRoles as $r) {
                if ($role == $r[$index_to_check]) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function getPermissions()
    {
        if (!is_null($this->cachedRoles)) {
            return $this->cachedPermissions;
        }

        $this->cachedRoles = Helpers::getCachedValue(
            'larapress.users.'.$this->id.'.roles.all',
            ['user.permissions:'.$this->id],
            86400,
            true,
            function () {
                return $this->roles()->with('permissions')->get()->toArray();
            },
            null
        );

        $this->cachedPermissions = [];
        foreach ($this->cachedRoles as $role) {
            foreach ($role['permissions'] as $permission) {
                $this->cachedPermissions[] = [$permission['id'], $permission['name'].'.'.$permission['verb']];
            }
        }

        return $this->cachedPermissions;
    }

    /**
     * Removes all cached permissions for this user.
     */
    public function forgetPermissionsCache()
    {
        Helpers::forgetCachedValues(['user.permissions:'.$this->id]);
    }
}
