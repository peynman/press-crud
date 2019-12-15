<?php

namespace Larapress\CRUD\CRUD;

use Larapress\Core\Extend\Helpers;
use Larapress\CRUD\Base\BaseCRUDProvider;
use Larapress\CRUD\Base\ICRUDProvider;
use Larapress\Profiles\Models\Role;

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
    public $autoSyncRelations = ['permissions'];
    public $validSortColumns = ['id', 'name', 'title', 'created_at'];
    public $validRelations = [];
    public $validFilters = [];
    public $defaultShowRelations = ['permissions'];
    public $excludeFromUpdate = ['name'];
    public $searchColumns = ['name', 'title'];
    public $filterDefaults = [];
    public $filterFields = [];

    public function onBeforeCreate($args)
    {
        if (isset($args['permissions'])) {
            $args['permissions'] = Helpers::getNormalizedObjectIds($args['permissions']);
        }

        return $args;
    }

    public function onBeforeUpdate($args)
    {
        return $this->onBeforeCreate($args);
    }

    /**
     * @param Role $object
     * @param array  $input_data
     *
     * @return object
     */
    public function onAfterUpdate($object, $input_data)
    {
        return $object;
    }
}
