<?php

namespace Larapress\CRUD\Services\RBAC;

/**
 * Undocumented interface
 */
interface IPermissionsMetadata
{
    /***
     * get permissions required for each CRUD operation
     *
     * @return array
     */
    public function getPermissionVerbs(): array;

    /**
     * Permission group name.
     *
     * @return string
     */
    public function getPermissionObjectName(): string;
}
