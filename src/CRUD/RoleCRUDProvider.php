<?php

namespace Larapress\CRUD\CRUD;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Larapress\CRUD\Services\CRUD\Traits\CRUDProviderTrait;
use Larapress\CRUD\Services\CRUD\ICRUDProvider;
use Larapress\CRUD\Models\Role;
use Larapress\CRUD\ICRUDUser;
use Larapress\CRUD\Services\CRUD\ICRUDVerb;
use Larapress\CRUD\Services\CRUD\Traits\CRUDRelationSyncTrait;

class RoleCRUDProvider implements ICRUDProvider
{
    use CRUDProviderTrait;
    use CRUDRelationSyncTrait;

    public $name_in_config = 'larapress.crud.routes.roles.name';
    public $model_in_config = 'larapress.crud.routes.roles.model';
    public $compositions_in_config = 'larapress.crud.routes.roles.compositions';

    public $verbs = [
        ICRUDVerb::VIEW,
        ICRUDVerb::SHOW,
        ICRUDVerb::CREATE,
        ICRUDVerb::EDIT,
        ICRUDVerb::DELETE,
    ];
    public $createValidations = [
        'name' => 'required|string|unique:roles,name|max:190|regex:/(^[A-Za-z0-9-_.]+$)+/',
        'title' => 'required|string',
        'permissions.*' => 'nullable|exists:permissions,id',
        'priority' => 'required|numeric',
    ];
    public $validSortColumns = [
        'id',
        'name',
        'title',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    public $defaultShowRelations = [
        'permissions',
    ];
    public $searchColumns = [
        'name',
        'title',
    ];

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getValidRelations(): array {
        return [
            'permissions' => null,
            'author' => config('larapress.crud.user.provider'),
        ];
    }

    /**
     * Exclude current id in name unique request
     *
     * @param Request $request
     *
     * @return array
     */
    public function getUpdateRules(Request $request): array
    {
        /** @var ICRUDUser */
        $user = Auth::user();

        $updateValidations = [
            'name' => 'required|string|max:190|regex:/(^[A-Za-z0-9-_.]+$)+/|unique:roles,name',
            'title' => 'required|string',
            'permissions.*' => 'nullable|exists:permissions,id',
            'priority' => 'required|numeric',
        ];

        $updateValidations['name'] .= ',' . $request->route('id');
        $updateValidations['priority'] .= '|lte:' . (is_null($user) ? 0 : $user->getUserHighestRole()->priority);
        return $updateValidations;
    }

    /**
     * Undocumented function
     *
     * @param array $args
     * @return array
     */
    public function onBeforeCreate(array $args): array
    {
        $args['author_id'] = Auth::user()->id;

        return $args;
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function onBeforeQuery(Builder $query): Builder
    {
        /** @var ICRUDUser */
        $user = Auth::user();

        if (! $user->hasRole(config('larapress.profiles.security.roles.super_role'))) {
            $query->where('priority', '>=', $user->getUserHighestRole()->priority);
        }

        return $query;
    }

    /**
     * @param Role $object
     *
     * @return bool
     */
    public function onBeforeAccess($object): bool
    {
        /** @var ICRUDUser */
        $user = Auth::user();

        if (! $user->hasRole(config('larapress.profiles.security.roles.super_role'))) {
            return $user->getUserHighestRole()->priority >= $object->priority;
        }

        return true;
    }

    /**
     * Undocumented function
     *
     * @param $object
     * @param array $input_data
     *
     * @return void
     */
    public function onAfterCreate($object, array $input_data): void
    {
        if (!empty($input_data['permissions'])) {
            $this->syncBelongsToManyRelation('permissions', $object, $input_data);
        }
    }

    /**
     * Undocumented function
     *
     * @param $object
     * @param array $input_data

     * @return void
     */
    public function onAfterUpdate($object, array $input_data): void
    {
        if (!empty($input_data['permissions'])) {
            $this->syncBelongsToManyRelation('permissions', $object, $input_data);
        }

        // forget cached permission for users with this role
        $object->users()->chunk(100, function ($users) {
            foreach ($users as $user) {
                $user->forgetPermissionsCache();
            }
        });
    }
}
