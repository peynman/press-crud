<?php

namespace Larapress\CRUD\Repository;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Larapress\Core\Extend\Helpers;
use Larapress\CRUD\ICRUDUser;
use Larapress\CRUD\Models\Role;
use Larapress\Profiles\IProfileUser;
use Illuminate\Database\Eloquent\Model;

class RoleRepository implements IRoleRepository
{

    /**
     * @return \Larapress\CRUD\Models\Role[]
     */
    public function getAllRoles() {
        return Role::all(['id', 'title']);
    }

    /**
     * @param IProfileUser|ICRUDUser $user
     * @return \Larapress\CRUD\Models\Role[]
     */
    public function getVisibleRoles($user)
    {
        return Role::where('priority', '>=', $this->getUserHighestRole($user)->priority)->get();
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
