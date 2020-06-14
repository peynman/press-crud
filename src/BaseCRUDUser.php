<?php

namespace Larapress\CRUD;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Larapress\CRUD\Models\Permission;
use Larapress\CRUD\Models\Role;

trait BaseCRUDUser
{
    /** @var array */
    public $permissions = null;

    /**
     * Removes all cached permissions for this user.
     */
    public function forgetPermissionsCache()
    {
        Cache::forget("larapress.cached..user.$this->id.permissions");
    }

    /**
     * Check if user has permission or not.
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
     * @param string|string[] $roles
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
     * @return array
     */
    public function getPermissions() {
        $this->checkPermission(''); // make sure cache is up
        return $this->permissions;
    }

    /**
     * @param string|int|Permission $permission
     *
     * @return bool
     */
    protected function checkPermission($permission)
    {
        if (is_null($this->permissions)) {
            $this->permissions = Cache::get("larapress.cached..user.$this->id.permissions");
            if (is_null($this->permissions)) {
                $perms = [];
                /** @var Role[] $roles */
                $roles = $this->roles()->with('permissions')->get();
                foreach ($roles as $role) {
                    foreach ($role->permissions as $role_permission) {
                        $perms[] = [$role_permission->id, $role_permission->name.'.'.$role_permission->verb];
                    }
                }
                Cache::tags(['permissions'])->put(
                    "larapress.cached..user.$this->id.permissions",
                    $perms,
                    Carbon::now()->addHours(12)
                );
                $this->permissions = $perms;
            }
        }
        if (is_object($permission)) {
            foreach ($this->permissions as $my_permission) {
                if ($my_permission[0] === $permission->id) {
                    return true;
                }
            }
        } else {
            $index_to_check = 1;
            if (is_numeric($permission)) {
                $index_to_check = 0;
            }
            foreach ($this->permissions as $my_permission) {
                if ($my_permission[$index_to_check] === $permission) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param string|int|Role $role
     *
     * @return bool
     */
    protected function checkRole($role)
    {
        foreach ($this->roles as $r) {
            if ($role == $r->name || $role === $r->id) {
                return true;
            }
        }

        return false;
    }

    public static function forgetAllPermissionsCache()
    {
        Cache::tags(['permissions'])->flush();
    }
}
