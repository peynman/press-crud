<?php

namespace Larapress\CRUD\Repository;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Larapress\CRUD\Extend\Helpers;
use Larapress\CRUD\ICRUDUser;
use Larapress\CRUD\Models\Permission;
use Larapress\CRUD\Models\Role;
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
            'larapress.users.'.$user->id.'.permissions',
            function() use($user) {
                $mypermissions = array_map(function($p) { return $p[0]; }, $user->getPermissions());
                return Permission::whereIn('id', $mypermissions)->get();
            },
            ['roles', 'user:'.$user->id],
            null
        );
    }
}
