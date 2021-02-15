<?php

namespace Larapress\CRUD\CRUD;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Larapress\CRUD\Services\BaseCRUDProvider;
use Larapress\CRUD\Services\ICRUDProvider;
use Larapress\CRUD\Services\IPermissionsMetadata;
use Larapress\CRUD\Models\Role;
use Larapress\CRUD\ICRUDUser;

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
    public $validSortColumns = [
        'id',
        'name',
        'title',
        'created_at',
        'updated_at',
    ];
    public $validRelations = [
        'permissions',
    ];
    public $defaultShowRelations = [
        'permissions',
    ];
    public $searchColumns = [
        'name',
        'title',
    ];
    /**
     * Exclude current id in name unique request
     *
     * @param Request $request
     * @return void
     */
    public function getUpdateRules(Request $request)
    {
        /** @var ICRUDUser */
        $user = Auth::user();

        $updateValidations = [
            'name' => 'required|string|max:190|regex:/(^[A-Za-z0-9-_.]+$)+/|unique:roles,name',
            'title' => 'required|string',
            'permissions.*.id' => 'nullable|exists:permissions,id',
            'priority' => 'required|numeric',
        ];

        $updateValidations['name'] .= ',' . $request->route('id');
        $updateValidations['priority'] .= '|lte:' . $user->getUserHighestRole()->priority;
        return $updateValidations;
    }


    /**
     * @param Builder $query
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function onBeforeQuery($query)
    {
        /** @var ICRUDUser */
        $user = Auth::user();

        if (! $this->user->hasRole(config('larapress.profiles.security.roles.super-role'))) {
            $query->where('priority', '>=', $user->getUserHighestRole()->priority);
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
        /** @var ICRUDUser */
        $user = Auth::user();

        if (! $user->hasRole(config('larapress.profiles.security.roles.super-role'))) {
            return $user->getUserHighestRole()->priority >= $object->priority;
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
        if (!empty($input_data['permissions'])) {
            $this->syncBelongsToManyRelation('permissions', $object, $input_data);
        }

        // @todo: add cache reset for users with this role
        $object->users()->chunk(100, function ($users) {
            foreach ($users as $user) {
                $user->forgetPermissionsCache();
            }
        });
    }
}
