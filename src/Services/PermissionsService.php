<?php

namespace Larapress\CRUD\Services;

use Illuminate\Support\Facades\Hash;
use Larapress\CRUD\Models\Permission;
use Larapress\CRUD\Models\Role;
use Illuminate\Support\Str;

class PermissionsService implements IPermissionsService
{
    /**
     * Undocumented function
     *
     * @param callable $callback
     * @return void
     */
    public function forEachRegisteredProviderClass($callback)
    {
        $process_class_names = function ($meta_data_classes, $iterate) use ($callback) {
            if (is_array($meta_data_classes)) {
                foreach ($meta_data_classes as $meta_data_class) {
                    if (Str::startsWith($meta_data_class, 'include::')) {
                        $iterate(config(Str::substr($meta_data_class, Str::length('include::'))), $iterate);
                    } else {
                        $callback($meta_data_class);
                    }
                }
            }
        };
        $process_class_names(config('larapress.crud.permissions'), $process_class_names);
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function updateSuperRole()
    {
        /** @var Role $super_role */
        $super_role = Role::where('name', 'super-role')->first();
        if (is_null($super_role)) {
            $super_role = Role::create([
                'name' => 'super-role',
                'title' => 'Super Role',
                'priority' => $this->getSuperRolePriority(),
            ]);
        }
        /** @var int[] $permission_ids */
        $permission_ids = Permission::query()->select('id')->pluck('id');
        $super_role->permissions()->sync($permission_ids);

        /** @var Builder $user_query */
        $user_query = call_user_func([config('larapress.crud.user.class'), 'query']);
        /** @var ICRUDUser[] $super_users */
        $super_users = $user_query->whereHas(
            'roles',
            function (/* @var Builder $q */$q) {
                $q->where('name', 'super-role');
            }
        )->get();
        return $super_users;
    }

    /**
     * Undocumented function
     *
     * @return int
     */
    public function getSuperRolePriority()
    {
        return 4294967295;
    }


    /**
     * Undocumented function
     *
     * @param string $username
     * @param string $password
     * @return mixed
     */
    public function createSuperUser($username, $password)
    {
        /** @var Builder $user_quer */
        $user_quer = call_user_func([config('larapress.crud.user.class'), 'query']);
        /** @var \Larapress\CRUD\ICRUDUser $user */
        $user = $user_quer->where('name', $username)->first();

        if (is_null($user)) {
            $user = call_user_func([config('larapress.crud.user.class'), 'create'], [
                'name' => $username,
                'password' => Hash::make($password),
            ]);
        } else {
            if ($password) {
                $user->update([
                    'password' => Hash::make($password),
                ]);
            }
        }

        /** @var Role $super_role */
        $super_role = Role::where('name', 'super-role')->first();
        if (is_null($super_role)) {
            $super_role = Role::create([
                'name' => 'super-role',
                'title' => 'Super Role',
            ]);
        }
        if (!is_null($super_role)) {
            $user->roles()->sync($super_role);
        }

        /** @var int[] $permission_ids */
        $permission_ids = Permission::query()->select('id')->pluck('id');
        $super_role->permissions()->sync($permission_ids);
        $user->forgetPermissionsCache();
        return $user;
    }
}
