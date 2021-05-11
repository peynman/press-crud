<?php

namespace Larapress\CRUD\Services\CRUD;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

trait BaseCRUDProvider
{
    /**
     * @return object::class
     */
    public function getModelClass()
    {
        if (isset($this->model)) {
            return $this->model;
        } else {
            return isset($this->class_in_config) ? config($this->class_in_config) : [];
        }
    }

    /**
     * @return bool
     */
    public function shouldFilterRequestParamsByRules()
    {
        return true;
    }

    /**
     * Undocumented function
     *
     * @param mixed $object
     * @return void
     */
    public function getExportArray($object)
    {
        return [];
    }

    /**
     * Undocumented function
     *
     * @return IReportSource
     */
    public function getReportSources()
    {
        return $this->getMergedValuesForPropertyFromExtendedProviders(
            'reportSources',
            'getReportSources'
        );
    }

    /**
     * @return array
     */
    public function getValidRelations()
    {
        return $this->getMergedValuesForPropertyFromExtendedProviders(
            'validRelations',
            'getValidRelations'
        );
    }

    /**
     * @return array
     */
    public function getValidSortColumns()
    {
        return $this->getMergedValuesForPropertyFromExtendedProviders(
            'validSortColumns',
            'getValidSortColumns'
        );
    }

    /**
     * @return array
     */
    public function getSearchableColumns()
    {
        return $this->getMergedValuesForPropertyFromExtendedProviders(
            'searchColumns',
            'getSearchableColumns'
        );
    }

    /**
     * @return array
     */
    public function getAutoSyncRelations()
    {
        return $this->getMergedValuesForPropertyFromExtendedProviders(
            'autoSyncRelations',
            'getAutoSyncRelations'
        );
    }

    /**
     * @return array
     */
    public function getAutoCountRelations()
    {
        return $this->getMergedValuesForPropertyFromExtendedProviders(
            'autoCountRelations',
            'getAutoCountRelations'
        );
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getSummerizableColumns()
    {
        return $this->getMergedValuesForPropertyFromExtendedProviders(
            'summerizeColumns',
            'getSummerizableColumns'
        );
    }

    /**
     * @return array
     */
    public function getFilterFields()
    {
        return $this->getMergedValuesForPropertyFromExtendedProviders(
            'filterFields',
            'getFilterFields'
        );
    }

    /**
     * @return array
     */
    public function getFilterDefaultValues()
    {
        return $this->getMergedValuesForPropertyFromExtendedProviders(
            'filterDefaults',
            'getFilterDefaultValues'
        );
    }

    /**
     * @return array
     */
    public function getDeleteCascades()
    {
        return $this->getMergedValuesForPropertyFromExtendedProviders(
            'deleteCascades',
            'getDeleteCascades'
        );
    }

    /**
     * @return array
     */
    public function getEagerRelations()
    {
        return $this->getMergedValuesForPropertyFromExtendedProviders(
            'defaultShowRelations',
            'getEagerRelations'
        );
    }

    /**
     * @return array
     */
    public function getExcludeIfNull()
    {
        return $this->getMergedValuesForPropertyFromExtendedProviders(
            'excludeIfNull',
            'getExcludeIfNull'
        );
    }

    /**
     * @return array
     */
    public function getCreateRules(Request $request)
    {
        return isset($this->createValidations) ? $this->createValidations : [];
    }

    /**
     * @return array
     */
    public function getUpdateRules(Request $request)
    {
        return isset($this->updateValidations) ? $this->updateValidations : [];
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

    public function getPermissionVerbs()
    {
        return isset($this->verbs) ? $this->verbs : [];
    }

    public function getPermissionObjectName()
    {
        return isset($this->name_in_config) ? config($this->name_in_config) : $this->name;
    }

    /**
     * @param string $relation
     * @param Model $object
     * @param array $data
     * @param string $class
     */
    protected function saveHasManyRelation($relation, $object, $data, $class)
    {
        $models = [];
        foreach ($data[$relation] as $datum) {
            $models[] = new $class($datum);
        }
        /** @var \Illuminate\Database\Eloquent\Relations\HasMany $builder */
        $builder = call_user_func([$object, $relation]);
        $builder->saveMany($models);
    }

    /**
     * @param string $relation
     * @param Model $object
     * @param array $data
     */
    protected function syncWithoutDetachingBelongsToManyRelation($relation, $object, $data)
    {
        if (!empty($data[$relation])) {
            $ids = [];
            foreach ($data[$relation] as $datum) {
                $ids[] = $datum['id'];
            }

            /** @var \Illuminate\Database\Eloquent\Relations\BelongsToMany $builder */
            $builder = call_user_func([$object, $relation]);
            $builder->syncWithoutDetaching($ids);
        }
    }


    /**
     * @param string $relation
     * @param Model $object
     * @param array $data
     */
    protected function syncBelongsToManyRelation($relation, $object, $data, $callback = null, $attributes = null)
    {
        if (!empty($data[$relation])) {
            $ids = [];
            foreach ($data[$relation] as $datum) {
                if (is_null($callback)) {
                    if (is_null($attributes)) {
                        $ids[] = $datum['id'];
                    } else {
                        $ids[$datum['id']] = $attributes($datum);
                    }
                } else {
                    if ($callback($datum)) {
                        if (is_null($attributes)) {
                            $ids[] = $datum['id'];
                        } else {
                            $ids[$datum['id']] = $attributes($datum);
                        }
                    }
                }
            }

            /** @var \Illuminate\Database\Eloquent\Relations\BelongsToMany $builder */
            $builder = call_user_func([$object, $relation]);
            $builder->sync($ids);
        }
    }


    protected $extendedProviders = null;
    protected function getExtendedProviders()
    {
        if (! is_null($this->extendedProviders)) {
            return $this->extendedProviders;
        }

        $extended = [];
        if (isset($this->extend_in_config)) {
            $extends = config($this->extend_in_config);
            if ($extends && count($extends) > 0) {
                foreach ($extends as $extendedClass) {
                    if (is_string($extendedClass)) {
                        $extended[] = new $extendedClass();
                    }
                }
            }
        }

        $this->extendedProviders = $extended;
        return $extended;
    }

    /**
     * Undocumented function
     *
     * @param [type] $callback
     * @return void
     */
    protected function foreachExtendedProvider($callback)
    {
        /** @var ICRUDProvider[] */
        $extends = $this->getExtendedProviders();
        foreach ($extends as $extended) {
            $callback($extended);
        }
    }

    /**
     * Undocumented function
     *
     * @param string $internalArrayName
     * @param string $extendedFunctionName
     * @return array
     */
    protected function getMergedValuesForPropertyFromExtendedProviders($internalArrayName, $extendedFunctionName, $args = [])
    {
        if (property_exists($this, $internalArrayName)) {
            $merged = $this->{$internalArrayName};
        } else {
            $merged = [];
        }
        $this->foreachExtendedProvider(function (ICRUDProvider $provider) use (&$merged, $extendedFunctionName, $args) {
            $merged = array_merge($merged, call_user_func([$provider, $extendedFunctionName], ...$args));
        });
        return $merged;
    }
}
