<?php

namespace Larapress\CRUD\CRUD;

use Larapress\CRUD\Services\IPermissionsMetadata;

class SystemAppPermissions implements IPermissionsMetadata
{

    /***
     * get permissions required for each CRUD operation
     *
     * @return array
     */
    public function getPermissionVerbs()
    {
        return config('larapress.crud.app_permissions');
    }

    /**
     * Permission group name.
     *
     * @return string
     */
    public function getPermissionObjectName()
    {
        return 'app';
    }
}
