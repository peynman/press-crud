<?php

namespace Larapress\CRUD\Services\CRUD;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class CRUDProviderComposition implements ICRUDProvider
{
    /** @var ICRUDProvider */
    protected $sourceProvider;
    public function __construct($sourceProvider)
    {
        $this->sourceProvider = $sourceProvider;
    }

    /**
     * @return string
     */
    public function getModelClass(): string
    {
        return $this->sourceProvider->getModelClass();
    }

    /**
     * @param $id
     *
     * @return Model|null
     */
    public function getObjectFromID(int $id): Model|null
    {
        return $this->sourceProvider->getObjectFromID($id);
    }

    /**
     * Undocumented function
     *
     * @return string[]
     */
    public function getCompositionClasses(): array
    {
        return $this->sourceProvider->getCompositionClasses();
    }

    /**
     * @return bool
     */
    public function shouldFilterRequestParamsByRules(): bool
    {
        return $this->sourceProvider->shouldFilterRequestParamsByRules();
    }

    /**
     * Undocumented function
     *
     * @return ICRUDReportSource[]
     */
    public function getReportSources(): array
    {
        return $this->sourceProvider->getReportSources();
    }

    /**
     * @return array
     */
    public function getValidRelations(): array
    {
        return $this->sourceProvider->getValidRelations();
    }

    /**
     * @return array
     */
    public function getValidSortColumns(): array
    {
        return $this->sourceProvider->getValidSortColumns();
    }

    /**
     * @return array
     */
    public function getSearchableColumns(): array
    {
        return $this->sourceProvider->getSearchableColumns();
    }

    /**
     * @return array
     */
    public function getFilterFields(): array
    {
        return $this->sourceProvider->getFilterFields();
    }

    /**
     * @return array
     */
    public function getDefaultShowRelations(): array
    {
        return $this->sourceProvider->getDefaultShowRelations();
    }

    /**
     * @return array
     */
    public function getCreateRules(Request $request): array
    {
        return $this->sourceProvider->getCreateRules($request);
    }

    /**
     * @return array
     */
    public function getUpdateRules(Request $request): array
    {
        return $this->sourceProvider->getUpdateRules($request);
    }


    /**
     * @return bool
     */
    public function isExportable(): bool
    {
        return $this->sourceProvider->isExportable();
    }

    /**
     * Undocumented function
     *
     * @param $object
     * @return void
     */
    public function getExportArray($object): array
    {
        return $this->sourceProvider->getExportArray($object);
    }

    /**
     * @param $object
     *
     * @return array
     */
    public function getExportMap($object): array
    {
        return $this->sourceProvider->getExportMap($object);
    }

    /**
     * @return array
     */
    public function getExportColumnTypes(): array
    {
        return $this->sourceProvider->getExportColumnTypes();
    }

    /**
     * @return array
     */
    public function getExportHeaders(): array
    {
        return $this->sourceProvider->getExportHeaders();
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function onBeforeQuery(Builder $query): Builder
    {
        return $this->sourceProvider->onBeforeQuery($query);
    }

    /**
     * @param array $args
     *
     * @return array
     */
    public function onBeforeFilter(array $args): array
    {
        return $this->sourceProvider->onBeforeFilter($args);
    }

    /**
     * @param array $args
     *
     * @return array
     */
    public function onBeforeCreate(array $args): array
    {
        return $this->sourceProvider->onBeforeCreate($args);
    }

    /**
     * @param $object
     * @param $args
     *
     * @return array
     */
    public function onBeforeObjectUpdate($object, array $args): array
    {
        return $this->sourceProvider->onBeforeObjectUpdate($object, $args);
    }

    /**
     * @param object $object
     * @param array  $input_data
     *
     * @return void
     */
    public function onAfterCreate($object, array $input_data): void
    {
        $this->sourceProvider->onAfterCreate($object, $input_data);
    }

    /**
     * @param array $args
     *
     * @return array
     */
    public function onBeforeUpdate(array $args): array
    {
        return $this->sourceProvider->onBeforeUpdate($args);
    }

    /**
     * @param object $object
     * @param array  $input_data
     *
     * @return void
     */
    public function onAfterUpdate($object, array $input_data): void
    {
        $this->sourceProvider->onAfterUpdate($object, $input_data);
    }

    /**
     * @param object $object
     *
     * @return bool
     */
    public function onBeforeDestroy($object): bool
    {
        return $this->sourceProvider->onBeforeDestroy($object);
    }

    /**
     * @param $object
     *
     * @return bool
     */
    public function onBeforeAccess($object): bool
    {
        return $this->sourceProvider->onBeforeAccess($object);
    }

    /**
     * @param object $object
     *
     * @return void
     */
    public function onAfterDestroy($object): void
    {
        $this->sourceProvider->onAfterDestroy($object);
    }

    /**
     * @param Model $model
     *
     * @return Model
     */
    public function onShowModel($model): Model
    {
        return $this->sourceProvider->onShowModel($model);
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getPermissionVerbs(): array
    {
        return $this->sourceProvider->getPermissionVerbs();
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    public function getPermissionObjectName(): string
    {
        return $this->sourceProvider->getPermissionObjectName();
    }
}
