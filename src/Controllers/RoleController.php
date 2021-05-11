<?php

namespace Larapress\CRUD\Controllers;

use Larapress\CRUD\CRUD\RoleCRUDProvider;
use Larapress\CRUD\Services\CRUD\BaseCRUDController;

/**
 * Standard CRUD Controller for Role resource.
 *
 * @group Roles Management
 */
class RoleController extends BaseCRUDController
{
    public static function registerRoutes()
    {
        parent::registerCrudRoutes(
            config('larapress.crud.routes.roles.name'),
            self::class,
            RoleCRUDProvider::class
        );
    }
}
