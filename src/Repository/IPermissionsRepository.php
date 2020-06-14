<?php

namespace Larapress\CRUD\Repository;

use Larapress\CRUD\ICRUDUser;
use Larapress\Profiles\IProfileUser;

interface IPermissionsRepository
{
    /**
     * @param IProfileUser|ICRUDUser $user
     * @return \Larapress\CRUD\Models\Role[]
     */
    public function getVisiblePermissions($user);
}
