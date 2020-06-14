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
        return Permission::whereIn('id', array_keys($user->getPermissions()))->get();
    }
}
