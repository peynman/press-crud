<?php

namespace Larapress\CRUD\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Interface ICRUDProvider.
 */
interface ICRUDProvider
{
    /**
     * @return string
     */
    public function getModelClass();

    /**
     * Undocumented function
     *
     * @return IReportSource[]
     */
    public function getReportSources();

    /**
     * @return bool
     */
    public function shouldFilterRequestParamsByRules();

    /**
     * @return array
     */
    public function getValidRelations();

    /**
     * @return array
     */
    public function getValidSortColumns();

    /**
     * @return array
     */
    public function getSearchableColumns();

    /**
     * @return array
     */
    public function getFilterFields();

    /**
     * @return array
     */
    public function getFilterDefaultValues();

    /**
     * @return array
     */
    public function getAutoSyncRelations();

    /**
     * @return array
     */
    public function getAutoCountRelations();

    /**
     * @return array
     */
    public function getEagerRelations();

    /**
     * @return array
     */
    public function getExcludeIfNull();

    /**
     * Undocumented function
     *
     * @param Request $request
     * @return array
     */
    public function getCreateRules(Request $request);

    /**
     * @param Request $request
     * @return array
     */
    public function getUpdateRules(Request $request);

    /**
     * @return array
     */
    public function getTranslations();

    /**
     * @return array
     */
    public function getJSONFills();

    /**
     * @return array
     */
    public function getDeleteCascades();

    /**
     * Undocumented function
     *
     * @param mixed $object
     * @return array
     */
    public function getExportArray($object);

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function onBeforeQuery($query);

    /**
     * @param array $args
     *
     * @return array
     */
    public function onBeforeFilter($args);

    /**
     * @param array $args
     *
     * @return array
     */
    public function onBeforeCreate($args);

    /**
     * @param $object
     *
     * @return bool
     */
    public function onBeforeAccess($object);

    /**
     * @param object $object
     * @param array  $input_data
     *
     * @return array
     */
    public function onAfterCreate($object, $input_data);

    /**
     * @param array $args
     *
     * @return array
     */
    public function onBeforeUpdate($args);

    /**
     * @param $object
     * @param array $args
     *
     * @return array
     */
    public function onBeforeObjectUpdate($object, $args);

    /**
     * @param object $object
     * @param array  $input_data
     *
     * @return array
     */
    public function onAfterUpdate($object, $input_data);

    /**
     * @param $object
     */
    public function onAfterDestroy($object);

    /**
     * @param $object
     *
     * @return bool
     */
    public function onBeforeDestroy($object);

    /**
     * @param $object
     *
     * @return bool
     */
    public function onBeforeDestroyCascades($object);

    /**
     * @param $object
     */
    public function onAfterDestroyCascades($object);

    /**
     * @param Model $model
     *
     * @return Model|array
     */
    public function onShowModel($model);

    /**
     * @param $id
     *
     * @return Model|null
     */
    public function getObjectFromID($id);

    /**
     * @return bool
     */
    public function isExportable();

    /**
     * @param $object
     *
     * @return array|null
     */
    public function getExportMap($object);

    /**
     * @return array|null
     */
    public function getExportColumnTypes();

    /**
     * @return array|null
     */
    public function getExportHeaders();
}
