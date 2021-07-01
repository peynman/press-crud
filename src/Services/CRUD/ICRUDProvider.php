<?php

namespace Larapress\CRUD\Services\CRUD;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Larapress\CRUD\Services\RBAC\IPermissionsMetadata;

/**
 * Interface ICRUDProvider.
 */
interface ICRUDProvider extends IPermissionsMetadata
{
    /**
     * @return string
     */
    public function getModelClass(): string;

    /**
     * @param $id
     *
     * @return Model|null
     */
    public function getObjectFromID(int $id);

    /**
     * @return bool
     */
    public function shouldFilterRequestParamsByRules(): bool;

    /**
     * Undocumented function
     *
     * @return string[]
     */
    public function getCompositionClasses(): array;

    /**
     * Undocumented function
     *
     * @return ICRUDReportSource[]
     */
    public function getReportSources(): array;

    /**
     * @return array
     */
    public function getValidRelations(): array;

    /**
     * @return array
     */
    public function getValidSortColumns(): array;

    /**
     * @return array
     */
    public function getSearchableColumns(): array;

    /**
     * @return array
     */
    public function getFilterFields(): array;

    /**
     * @return array
     */
    public function getEagerRelations(): array;

    /**
     * Undocumented function
     *
     * @param Request $request
     * @return array
     */
    public function getCreateRules(Request $request): array;

    /**
     * @param Request $request
     * @return array
     */
    public function getUpdateRules(Request $request): array;

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function onBeforeQuery(Builder $query): Builder;

    /**
     * @param array $args
     *
     * @return array
     */
    public function onBeforeFilter(array $args): array;

    /**
     * @param array $args
     *
     * @return array
     */
    public function onBeforeCreate(array $args): array;

    /**
     * @param $object
     *
     * @return bool
     */
    public function onBeforeAccess($object): bool;

    /**
     * @param object $object
     * @param array  $input_data
     *
     * @return void
     */
    public function onAfterCreate($object, array $input_data): void;

    /**
     * @param array $args
     *
     * @return array
     */
    public function onBeforeUpdate(array $args): array;

    /**
     * @param $object
     * @param array $args
     *
     * @return array
     */
    public function onBeforeObjectUpdate($object, array $args): array;

    /**
     * @param object $object
     * @param array  $input_data
     *
     * @return void
     */
    public function onAfterUpdate($object, array $input_data): void;

    /**
     * @param $object
     *
     * @return void
     */
    public function onAfterDestroy($object): void;

    /**
     * @param $object
     *
     * @return bool
     */
    public function onBeforeDestroy($object): bool;

    /**
     * @param mixed $model
     *
     * @return Model|array
     */
    public function onShowModel($model);

    /**
     * Undocumented function
     *
     * @param $object
     * @return array
     */
    public function getExportArray($object): array;

    /**
     * @return bool
     */
    public function isExportable(): bool;

    /**
     * @param $object
     *
     * @return array
     */
    public function getExportMap($object): array;

    /**
     * @return array
     */
    public function getExportColumnTypes(): array;

    /**
     * @return array
     */
    public function getExportHeaders(): array;
}
