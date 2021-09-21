<?php

namespace Larapress\CRUD\Repository;

use Larapress\CRUD\Extend\Helpers;
use Larapress\CRUD\ICRUDUser;
use Larapress\CRUD\Models\Permission;
use Larapress\Profiles\IProfileUser;

class PermissionsRepository implements IPermissionsRepository
{
    /**
     * @param IProfileUser|ICRUDUser $user
     * @return \Larapress\CRUD\Models\Role[]
     */
    public function getVisiblePermissions($user)
    {
        return Helpers::getCachedValue(
            'larapress.crud.users.'.$user->id.'.permissions',
            ['user.permissions:'.$user->id],
            86400,
            false,
            function () use ($user) {
                $mypermissions = array_map(function ($p) {
                    return $p['id'];
                }, $user->getPermissions());
                return Permission::whereIn('id', $mypermissions)->get();
            },
        );
    }
}
