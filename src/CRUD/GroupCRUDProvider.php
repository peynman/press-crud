<?php

namespace Larapress\CRUD\CRUD;

use Illuminate\Support\Facades\Auth;
use Larapress\CRUD\Services\CRUD\BaseCRUDProvider;
use Larapress\CRUD\Services\CRUD\ICRUDProvider;
use Larapress\CRUD\Services\RBAC\IPermissionsMetadata;
use Larapress\CRUD\ICRUDUser;
use Larapress\CRUD\Models\Group;

class GroupCRUDProvider implements ICRUDProvider, IPermissionsMetadata
{
    use BaseCRUDProvider;

    public $name_in_config = 'larapress.crud.routes.groups.name';
    public $verbs = [
        self::VIEW,
        self::CREATE,
        self::EDIT,
        self::DELETE,
    ];
    public $model = Group::class;
    public $createValidations = [
    ];
    public $updateValidations = [
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
     * @param Builder $query
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function onBeforeQuery($query)
    {
        /** @var ICRUDUser */
        $user = Auth::user();

        if (! $this->user->hasRole(config('larapress.profiles.security.roles.super-role'))) {
            $query->whereHas('owner', function ($q) use ($user) {
                $q->whereIn('id', $user->id);
            });
        }

        return $query;
    }

    /**
     * @param Group $object
     *
     * @return bool
     */
    public function onBeforeAccess($object)
    {
        /** @var ICRUDUser */
        $user = Auth::user();

        if (! $user->hasRole(config('larapress.profiles.security.roles.super-role'))) {
            return in_array($user->id, $object->getOwnerIdsAttribute());
        }

        return true;
    }
}
