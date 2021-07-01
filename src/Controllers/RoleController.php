<?php

namespace Larapress\CRUD\Controllers;

use Larapress\CRUD\CRUD\RoleCRUDProvider;
use Larapress\CRUD\Services\CRUD\CRUDController;

/**
 * Standard CRUD Controller for Role resource.
 *
 * @group Roles Management
 */
class RoleController extends CRUDController
{
    public static function registerRoutes()
    {
        parent::registerCrudRoutes(
            config('larapress.crud.routes.roles.name'),
            self::class,
            config('larapress.crud.routes.roles.provider'),
        );
    }
}
