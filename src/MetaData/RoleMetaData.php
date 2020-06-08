<?php

namespace Larapress\CRUD\Metadata;

use Larapress\CRUD\Base\BasePermissionMetadata;
use Larapress\CRUD\Base\IPermissionsMetadata;
use Larapress\CRUD\Base\SingleSourceBaseMetadata;

class RoleMetadata extends SingleSourceBaseMetadata implements
    IPermissionsMetadata
{
    use BasePermissionMetadata;

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
