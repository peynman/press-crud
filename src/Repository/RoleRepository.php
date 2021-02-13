<?php

namespace Larapress\CRUD\Repository;

use Larapress\CRUD\ICRUDUser;
use Larapress\CRUD\Models\Role;

class RoleRepository implements IRoleRepository
{
    /**
     * @param ICRUDUser $user
     * @return \Larapress\CRUD\Models\Role[]
     */
    public function getVisibleRoles($user)
    {
        return Role::where('priority', '<=', $user->getUserHighestRole()->priority)->get();
    }


    /**
     * @param ICRUDUser $user
     * @return \Larapress\CRUD\Models\Role|null
     */
    public function getUserHighestRole($user)
    {
        return $user->getUserHighestRole();
    }
}
