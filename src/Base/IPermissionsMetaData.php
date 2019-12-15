<?php

namespace Larapress\CRUD\Base;

interface IPermissionsMetaData
{
    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'destroy';
    const CREATE = 'create';
    const REPORTS = 'reports';
    const SYNC = 'sync';

    /***
     * get permissions required for each CRUD operation
     *
     * @return array
     */
    public function getPermissionVerbs();

    /**
     * Permission group name
     *
     * @return string
     */
    public function getPermissionObjectName();

    /**
     * @return string
     */
    public function getViewPermission();
    /**
     * @return string
     */
    public function getEditPermission();
    /**
     * @return string
     */
    public function getDeletePermission();
    /**
     * @return string
     */
    public function getCreatePermission();
    /**
     * @return string
     */
    public function getViewReportsPermission();
}
