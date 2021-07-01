<?php

namespace Larapress\CRUD\CRUD;

use Larapress\CRUD\Services\RBAC\IPermissionsMetadata;

class SystemAppPermissions implements IPermissionsMetadata
{

    /***
     * get permissions required for each CRUD operation
     *
     * @return array
     */
    public function getPermissionVerbs(): array
    {
        return config('larapress.crud.app_permissions');
    }

    /**
     * Permission group name.
     *
     * @return string
     */
    public function getPermissionObjectName(): string
    {
        return 'app';
    }
}
