<?php

namespace Larapress\CRUD\CRUD;

use Illuminate\Http\Request;
use Larapress\CRUD\Base\BaseCRUDProvider;
use Larapress\CRUD\Base\ICRUDProvider;
use Larapress\CRUD\Base\IPermissionsMetadata;
use Larapress\CRUD\Models\Role;

class RoleCRUDProvider implements ICRUDProvider, IPermissionsMetadata
{
    use BaseCRUDProvider;

    public $name_in_config = 'larapress.crud.routes.roles.name';
    public $verbs = [
        self::VIEW,
        self::CREATE,
        self::EDIT,
        self::DELETE,
    ];
    public $model = Role::class;
    public $createValidations = [
        'name' => 'required|string|unique:roles,name|max:190|regex:/(^[A-Za-z0-9-_.]+$)+/',
        'title' => 'required|string',
        'permissions.*.id' => 'nullable|exists:permissions,id',
    ];
    public $updateValidations = [
        'name' => 'required|string|max:190|regex:/(^[A-Za-z0-9-_.]+$)+/|unique:roles,name',
        'title' => 'required|string',
        'permissions.*.id' => 'nullable|exists:permissions,id',
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

    /**
     * Exclude current id in name unique request
     *
     * @param Request $request
     * @return void
     */
    public function getUpdateRules(Request $request) {
        $this->updateValidations['name'] .= ',' . $request->route('id');
        return $this->updateValidations;
    }
}
