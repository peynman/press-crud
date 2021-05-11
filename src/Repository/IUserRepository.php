<?php

namespace Larapress\CRUD\Repository;

use Larapress\CRUD\ICRUDUser;

interface IUserRepository
{
    public function getVisibleUsers(ICRUDUser $user, $filters = [],  $with = [], $append = []);
}
