<?php

namespace Larapress\CRUD\CRUD;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Larapress\CRUD\Services\BaseCRUDProvider;
use Larapress\CRUD\Services\ICRUDProvider;
use Larapress\CRUD\Services\IPermissionsMetadata;
use Larapress\CRUD\Models\Role;
use Larapress\CRUD\Repository\IRoleRepository;

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
        'priority' => 'required|numeric',
    ];
    public $updateValidations = [
        'name' => 'required|string|max:190|regex:/(^[A-Za-z0-9-_.]+$)+/|unique:roles,name',
        'title' => 'required|string',
        'permissions.*.id' => 'nullable|exists:permissions,id',
        'priority' => 'required|numeric',
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
    public $excludeIfNull = [
    ];
    public $searchColumns = [
        'name',
        'title',
    ];
    public $filterDefaults = [

    ];
    public $filterFields = [

    ];

    /** @var IRoleRepository */
    protected $repo;

    /** @var ICRUDUser */
    protected $user;

    /**
     * Exclude current id in name unique request
     *
     * @param Request $request
     * @return void
     */
    public function getUpdateRules(Request $request) {
        $this->repo = app(IRoleRepository::class);
        $this->user = Auth::user();

        $this->updateValidations['name'] .= ',' . $request->route('id');
        $this->updateValidations['priority'] .= '|lte:' . $this->repo->getUserHighestRole($this->user)->priority;
        return $this->updateValidations;
    }


    /**
     * @param Builder $query
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function onBeforeQuery($query)
    {
        $this->repo = app(IRoleRepository::class);
        $this->user = Auth::user();

        if (! $this->user->hasRole(config('larapress.profiles.security.roles.super-role'))) {
            $query->where('priority', '>=', $this->repo->getUserHighestRole($this->user)->priority);
        }

        return $query;
    }

    /**
     * @param Role $object
     *
     * @return bool
     */
    public function onBeforeAccess($object)
    {
        $this->repo = app(IRoleRepository::class);
        $this->user = Auth::user();
        if (! $this->user->hasRole(config('larapress.profiles.security.roles.super-role'))) {
            return $this->repo->getUserHighestRole($this->user)->priority >= $object->priority;
        }

        return true;
    }

    /**
     * Undocumented function
     *
     * @param [type] $object
     * @param [type] $input_data
     * @return void
     */
    public function onAfterCreate($object, $input_data)
    {
        $this->user = Auth::user();
        if (!empty($input_data['permissions'])) {
            $this->syncBelongsToManyRelation('permissions', $object, $input_data['permissions']);
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $object
     * @param [type] $input_data
     * @return void
     */
    public function onAfterUpdate($object, $input_data)
    {
        $this->user = Auth::user();
        if (!empty($input_data['permissions'])) {
            $this->syncBelongsToManyRelation('permissions', $object, $input_data);
        }

        // @todo: add cache reset for users with this role
        $object->users()->chunk(100, function($users) {
            foreach ($users as $user) {
                $user->forgetPermissionsCache();
            }
        });
    }

}
