<?php


namespace Larapress\CRUD\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

trait BaseCRUDProvider
{
    /**
     * @return array
     */
    public function getTranslations()
    {
        return isset($this->translations) ? $this->translations:[];
    }

    /**
     * @return array
     */
    public function getJSONFills()
    {
        return isset($this->jsons) ? $this->jsons:[];
    }

    /**
     * @return object::class
     */
    public function getModelClass()
    {
        return $this->model;
    }

    /**
     * @return bool
     */
    public function shouldFilterRequestParamsByRules()
    {
        return true;
    }

    /**
     * @return array
     */
    public function getValidRelations()
    {
        return isset($this->validRelations) ? $this->validRelations: [];
    }

    /**
     * @return array
     */
    public function getValidSortColumns()
    {
        return isset($this->validSortColumns) ? $this->validSortColumns:[];
    }

    /**
     * @return array
     */
    public function getSearchableColumns()
    {
        return isset($this->searchColumns) ? $this->searchColumns:[];
    }

    /**
     * @return array
     */
    public function getAutoSyncRelations()
    {
        return isset($this->autoSyncRelations) ? $this->autoSyncRelations:[];
    }

    /**
     * @return array
     */
    public function getAutoCountRelations()
    {
        return isset($this->autoCountRelations) ? $this->autoCountRelations: [];
    }

    /**
     * @return array
     */
    public function getFilterFields()
    {
        return isset($this->filterFields) ? $this->filterFields:[];
    }

    /**
     * @return array
     */
    public function getFilterDefaultValues()
    {
        return isset($this->filterDefaults) ? $this->filterDefaults:[];
    }

    /**
     * @return array
     */
    public function getDeleteCascades()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getEagerRelations()
    {
        return isset($this->defaultShowRelations) ? $this->defaultShowRelations:[];
    }

    /**
     * @return array
     */
    public function getExcludeUpdate()
    {
        return isset($this->excludeFromUpdate) ? $this->excludeFromUpdate:[];
    }

    /**
     * @return array
     */
    public function getCreateRules()
    {
        return isset($this->createValidations) ? $this->createValidations:[];
    }

    /**
     * @return array
     */
    public function getUpdateRules()
    {
        return isset($this->updateValidations) ? $this->updateValidations:[];
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function onBeforeQuery($query)
    {
        return $query;
    }

    /**
     * @param array $args
     *
     * @return array
     */
    public function onBeforeFilter($args)
    {
        return $args;
    }

    /**
     * @param array $args
     *
     * @return array
     */
    public function onBeforeCreate($args)
    {
        return $args;
    }

    /**
     * @param $object
     * @param $args
     *
     * @return mixed
     */
    public function onBeforeObjectUpdate($object, $args)
    {
        return $args;
    }

    /**
     * @param object $object
     * @param array  $input_data
     */
    public function onAfterCreate($object, $input_data)
    {
    }

    /**
     * @param array $args
     *
     * @return array
     */
    public function onBeforeUpdate($args)
    {
        return $args;
    }

    /**
     * @param object $object
     * @param array  $input_data
     */
    public function onAfterUpdate($object, $input_data)
    {
    }

    /**
     * @param object $object
     *
     * @return bool
     */
    public function onBeforeDestroy($object)
    {
        return true;
    }

    /**
     * @param $object
     *
     * @return bool
     */
    public function onBeforeAccess($object)
    {
        return true;
    }

    /**
     * @param object $object
     */
    public function onAfterDestroy($object)
    {
    }

    /**
     * @param $object
     *
     * @return bool
     */
    public function onBeforeDestroyCascades($object)
    {
        return true;
    }

    /**
     * @param $object
     */
    public function onAfterDestroyCascades($object)
    {
    }

    /**
     * @param Model $model
     *
     * @return Model
     */
    public function onShowModel($model)
    {
        return $model;
    }

    /**
     * @param $id
     *
     * @return Model|null
     */
    public function getObjectFromID($id)
    {
        return call_user_func([$this->getModelClass(), 'find'], $id);
    }

    /**
     * @return bool
     */
    public function isExportable()
    {
        return false;
    }

    /**
     * @param $object
     *
     * @return array
     */
    public function getExportMap($object)
    {
        return [];
    }

    /**
     * @return array
     */
    public function getExportColumnTypes()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getExportHeaders()
    {
        return [];
    }
}
