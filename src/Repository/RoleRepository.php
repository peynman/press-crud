<?php

namespace Larapress\CRUD\Repository;

use Carbon\Carbon;
use Larapress\CRUD\Extend\Helpers;
use Larapress\CRUD\ICRUDUser;
use Larapress\CRUD\Models\Role;
use Larapress\Profiles\IProfileUser;

class RoleRepository implements IRoleRepository
{
    /**
     * @param IProfileUser|ICRUDUser $user
     * @return \Larapress\CRUD\Models\Role[]
     */
    public function getVisibleRoles($user)
    {
        return Role::where('priority', '<=', $this->getUserHighestRole($user)->priority)->get();
    }

    /**
     * @param IProfileUser|ICRUDUser $user
     * @return \Larapress\CRUD\Models\Role|null
     */
    public function getUserHighestRole($user)
    {
        return Helpers::getCachedValue(
            'larapress.users.'.$user->id.'.roles.highest',
            function() use ($user) {
                return $user->roles()->orderBy('priority', 'DESC')->first();
            },
            ['larapress', 'roles'],
            Carbon::now()->addHours(1)
        );
    }
}
