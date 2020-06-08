<?php

namespace Larapress\CRUD\Repository;

use Larapress\CRUD\ICRUDUser;
use Larapress\Profiles\IProfileUser;

interface IRoleRepository
{
    /**
     * @return \Larapress\CRUD\Models\Role[]
     */
    public function getAllRoles();

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
