<?php

namespace Larapress\CRUD\CRUD;

use Larapress\CRUD\Base\BaseCRUDProvider;
use Larapress\CRUD\Base\ICRUDProvider;
use Larapress\CRUD\Models\Role;

class RoleCRUDProvider implements ICRUDProvider
{
    use BaseCRUDProvider;

    public $model = Role::class;
    public $createValidations = [
        'name' => 'required|string|unique:roles,name|max:190|regex:/(^[A-Za-z0-9-_.]+$)+/',
        'title' => 'required|string',
        'permissions' => 'required|objectIds:permissions,id,id',
    ];
    public $updateValidations = [
        'title' => 'required|string',
        'permissions' => 'required|objectIds:permissions,id,id',
    ];
    public $autoSyncRelations = [
        'permissions',
    ];
    public $validSortColumns = [
        'id',
        'name',
        'title',
        'created_at',
    ];
    public $validRelations = [
        'permissions',
    ];
    public $defaultShowRelations = [
        'permissions',
    ];
    public $validFilters = [];
    public $excludeFromUpdate = [
        'name',
    ];
    public $searchColumns = [
        'name',
        'title',
    ];
    public $filterDefaults = [

    ];
    public $filterFields = [

    ];
}
