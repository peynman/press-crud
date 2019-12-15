<?php

namespace Larapress\CRUD\CRUDControllers;

use Larapress\CRUD\CRUD\RoleCRUDProvider;

class RoleController extends BaseCRUDController
{
    public static function registerRoutes()
    {
        parent::registerCrudRoutes(
            config('larapress.profiles.routes.roles.name'),
            self::class,
            RoleCRUDProvider::class
        );
    }
}
