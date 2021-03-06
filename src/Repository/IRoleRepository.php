<?php

namespace Larapress\CRUD\Repository;

use Larapress\CRUD\ICRUDUser;
use Larapress\Profiles\IProfileUser;

interface IRoleRepository
{
    /**
     * @param IProfileUser|ICRUDUser $user
     * @return \Larapress\CRUD\Models\Role[]
     */
    public function getVisibleRoles($user);

    /**
     * @param IProfileUser|ICRUDUser $user
     * @return \Larapress\CRUD\Models\Role|null
     */
    public function getUserHighestRole($user);
}
