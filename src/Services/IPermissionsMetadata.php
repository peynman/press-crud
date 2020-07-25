<?php

namespace Larapress\CRUD\Services;

/**
 * Undocumented interface
 */
interface IPermissionsMetadata
{
    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'destroy';
    const CREATE = 'create';
    const REPORTS = 'reports';

    /***
     * get permissions required for each CRUD operation
     *
     * @return array
     */
    public function getPermissionVerbs();

    /**
     * Permission group name.
     *
     * @return string
     */
    public function getPermissionObjectName();
}
