<?php

namespace Larapress\CRUD\Controllers;

use Larapress\CRUD\CRUD\GroupCRUDProvider;
use Larapress\CRUD\Services\CRUD\BaseCRUDController;

/**
 * Standard CRUD Controller for Group resource.
 *
 * @group Groups Management
 */
class GroupController extends BaseCRUDController
{
    public static function registerRoutes()
    {
        parent::registerCrudRoutes(
            config('larapress.crud.routes.groups.name'),
            self::class,
            GroupCRUDProvider::class
        );
    }
}
