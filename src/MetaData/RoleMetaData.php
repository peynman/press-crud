<?php

namespace Larapress\CRUD\MetaData;

use Larapress\CRUD\Base\BasePermissionMetaData;
use Larapress\CRUD\Base\IPermissionsMetaData;
use Larapress\CRUD\Base\SingleSourceBaseMetaData;

class RoleMetaData extends SingleSourceBaseMetaData implements
    IPermissionsMetaData
{
    use BasePermissionMetaData;

    public function getPermissionVerbs()
    {
        return [
            self::VIEW,
            self::CREATE,
            self::EDIT,
            self::DELETE,
        ];
    }

    public function getPermissionObjectName()
    {
        return config('larapress.crud.routes.roles.name');
    }
}
