<?php

namespace Larapress\CRUD\Services\CRUD\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait CRUDProviderTrait
{
    /**
     * @return string
     */
    public function getModelClass(): string
    {
        if (isset($this->model)) {
            return $this->model;
        } else if (isset($this->model_in_config)) {
            return config($this->model_in_config);
        } else {
            return null;
        }
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
    public function shouldFilterRequestParamsByRules(): bool
    {
        return isset($this->shouldFilterRequestParams) ? $this->shouldFilterRequestParams : true;
    }


    /**
     * Undocumented function
     *
     * @return string[]
     */
    public function getCompositionClasses(): array
    {
        if (isset($this->compositions_in_config)) {
            return config($this->compositions_in_config, []);
        }

        return [];
    }

    /**
     * Undocumented function
     *
     * @return IReportSource
     */
    public function getReportSources(): array
    {
        return isset($this->reportSources) ? $this->reportSources : [];
    }

    /**
     * @return array
     */
    public function getValidRelations(): array
    {
        return isset($this->validRelations) ? $this->validRelations : [];
    }

    /**
     * @return array
     */
    public function getValidSortColumns(): array
    {
        return isset($this->validSortColumns) ? $this->validSortColumns : [];
    }

    /**
     * @return array
     */
    public function getSearchableColumns(): array
    {
        return isset($this->searchColumns) ? $this->searchColumns : [];
    }

    /**
     * @return array
     */
    public function getFilterFields(): array
    {
        return isset($this->filterFields) ? $this->filterFields : [];
    }

    /**
     * @return array
     */
    public function getEagerRelations(): array
    {
        return isset($this->defaultShowRelations) ? $this->defaultShowRelations : [];
    }

    /**
     * @return array
     */
    public function getCreateRules(Request $request): array
    {
        return isset($this->createValidations) ? $this->createValidations : [];
    }

    /**
     * @return array
     */
    public function getUpdateRules(Request $request): array
    {
        return isset($this->updateValidations) ? $this->updateValidations : [];
    }


    /**
     * @return bool
     */
    public function isExportable(): bool
    {
        return false;
    }

    /**
     * Undocumented function
     *
     * @param $object
     * @return void
     */
    public function getExportArray($object): array
    {
        return [];
    }

    /**
     * @param $object
     *
     * @return array
     */
    public function getExportMap($object): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getExportColumnTypes(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getExportHeaders(): array
    {
        return [];
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function onBeforeQuery(Builder $query): Builder
    {
        return $query;
    }

    /**
     * @param array $args
     *
     * @return array
     */
    public function onBeforeFilter(array $args): array
    {
        return $args;
    }

    /**
     * @param array $args
     *
     * @return array
     */
    public function onBeforeCreate(array $args): array
    {
        return $args;
    }

    /**
     * @param $object
     * @param $args
     *
     * @return array
     */
    public function onBeforeObjectUpdate($object, array $args): array
    {
        return $args;
    }

    /**
     * @param object $object
     * @param array  $input_data
     *
     * @return void
     */
    public function onAfterCreate($object, array $input_data): void
    {
    }

    /**
     * @param array $args
     *
     * @return array
     */
    public function onBeforeUpdate($args): array
    {
        return $args;
    }

    /**
     * @param object $object
     * @param array  $input_data
     */
    public function onAfterUpdate($object, array $input_data): void
    {
    }

    /**
     * @param object $object
     *
     * @return bool
     */
    public function onBeforeDestroy($object): bool
    {
        return true;
    }

    /**
     * @param $object
     *
     * @return bool
     */
    public function onBeforeAccess($object): bool
    {
        return true;
    }

    /**
     * @param object $object
     */
    public function onAfterDestroy($object): void
    {
    }

    /**
     * @param Model $model
     *
     * @return Model|array
     */
    public function onShowModel($model)
    {
        return $model;
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getPermissionVerbs(): array
    {
        return isset($this->verbs) ? $this->verbs : [];
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    public function getPermissionObjectName(): string
    {
        return isset($this->name_in_config) ? config($this->name_in_config) : $this->name;
    }
}
