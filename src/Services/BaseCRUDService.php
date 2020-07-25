<?php

namespace Larapress\CRUD\Services;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Larapress\CRUD\Exceptions\AppException;
use Larapress\CRUD\Exceptions\ValidationException;
use Larapress\CRUD\Events as CRUDEvent;

/**
 * Class BaseCRUDService.
 */
class BaseCRUDService implements ICRUDService
{
    /**
     * @var ICRUDProvider
     */
    public $crudProvider;
    /**
     * @var ICRUDExporter
     */
    public $crudExporter;
    /**
     * @var ICRUDStorage
     */
    public $crudStorage;

    /**
     * @param ICRUDProvider $provider
     */
    public function useProvider(ICRUDProvider $provider)
    {
        $this->crudProvider = $provider;
    }

    /**
     * @param ICRUDExporter $exporter
     */
    public function useCRUDExporter(ICRUDExporter $exporter)
    {
        $this->crudExporter = $exporter;
    }

    /**
     * @param ICRUDStorage $storage
     */
    public function useCRUDStorage(ICRUDStorage $storage) {
        $this->crudStorage = $storage;
    }

    /**
     * Search the resource.
     *
     * @param Request $request
     *
     * @return LengthAwarePaginator
     * @throws AppException
     */
    public function query(Request $request)
    {
        $query = $this->getQueryForRequest($request);
        $limit = $request->get('limit', 10);
        $models = $query->paginate($limit);

        $appends = $request->get('appends', []);
        if (isset($appends)) {
            foreach ($appends as $append) {
                if (isset($append['attribute'])) {
                    $models->getCollection()->makeVisible($append['attribute']);
                }
            }
        }

        return self::formatPaginatedResponse($request->all(['ref_id']), $models);
    }


    /**
     * Undocumented function
     *
     * @param Request $request
     * @return Builder
     * @throws AppException
     * @throws \Exception
     */
    public function getQueryForRequest(Request $request) {
        $query_string = $request->getContent();
        $query_params = json_decode($query_string, true);
        if (is_null($query_params)) {
            $query_params = [];
        }

        $query = $this->getQueryFromRequest($query_params);
        return $query;
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return Model
     * @throws \Larapress\Core\Exceptions\ValidationException
     * @throws AppException
     * @throws \Exception
     */
    public function store(Request $request)
    {
        $createRules = $this->crudProvider->getCreateRules($request);

        if ($this->crudProvider->shouldFilterRequestParamsByRules()) {
            $askedKeys = array_keys($createRules);
            $reqKeys = $request->keys();
            $keys = [];
            foreach ($askedKeys as $key) {
                $askKey = explode('.', $key)[0];
                if (in_array($askKey, $reqKeys)) {
                    $keys[] = $askKey;
                }
            }
            $input_data = $request->all($keys);
        } else {
            $input_data = $request->all();
        }

        $validate = Validator::make($input_data, $createRules);
        if ($validate->fails()) {
            throw new ValidationException($validate);
        }

        $exclude = $this->crudProvider->getExcludeIfNull();
        foreach ($exclude as $excluded) {
            if (isset($input_data[$excluded]) && is_null($input_data[$excluded])) {
                unset($input_data[$excluded]);
            }
        }
        $data = $this->crudProvider->onBeforeCreate($input_data);

        try {
            DB::beginTransaction();
            if (is_null($this->crudStorage)) {
                $object = call_user_func([$this->crudProvider->getModelClass(), 'create'], $data);
                self::syncRelations('store', $this->crudProvider->getAutoSyncRelations(), $object, $data);
            } else {
                $object = $this->crudStorage->store($request, $data);
            }

            $this->crudProvider->onAfterCreate($object, $input_data);

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        $with = $this->crudProvider->getEagerRelations();
        if (!is_null($with)) {
            $object->load($with);
        }

        event(new CRUDEvent\CRUDCreated($object, get_class($this->crudProvider), Carbon::now()));

        return $object;
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return Model
     * @throws AppException
     */
    public function show(Request $request, $id)
    {
        /**
         * @var Builder
         */
        $query = call_user_func([$this->crudProvider->getModelClass(), 'query']);
        $with = $this->crudProvider->getEagerRelations();
        if (!is_null($with)) {
            $query->with($with);
        }
        $model = $query->find($id);

        if (!$this->crudProvider->onBeforeAccess($model)) {
            throw new AppException(AppException::ERR_OBJ_ACCESS_DENIED);
        }

        $json_data = $this->crudProvider->getJSONFills();
        foreach ($json_data as $prefix => $items) {
            if (isset($model->$prefix)) {
                foreach ($items as $item) {
                    if (!empty($model->$prefix[$item])) {
                        $model[$prefix . '_' . $item] = $model->$prefix[$item];
                    }
                }
            }
        }

        $model = $this->crudProvider->onShowModel($model);

        return $model;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     * @throws ValidationException
     * @throws AppException
     * @throws \Exception
     */
    public function update(Request $request, $id)
    {
        $updateRules = $this->crudProvider->getUpdateRules($request);

        $input_data = null;
        if ($this->crudProvider->shouldFilterRequestParamsByRules()) {
            $askedKeys = array_keys($updateRules);
            $reqKeys = $request->keys();
            $keys = [];
            foreach ($askedKeys as $key) {
                $askKey = explode('.', $key)[0];
                if (in_array($askKey, $reqKeys)) {
                    $keys[] = $askKey;
                }
            }
            $input_data = $request->all($keys);
        } else {
            $input_data = $request->all();
        }

        $validate = Validator::make($input_data, $updateRules);
        if ($validate->fails()) {
            throw new ValidationException($validate);
        }

        $exclude = $this->crudProvider->getExcludeIfNull();
        foreach ($exclude as $excluded) {
            if (isset($input_data[$excluded]) && is_null($input_data[$excluded])) {
                unset($input_data[$excluded]);
            }
        }
        $data = $this->crudProvider->onBeforeUpdate($input_data);

        $object = $this->crudProvider->getObjectFromID($id);
        if (is_null($object)) {
            $object = call_user_func([$this->crudProvider->getModelClass(), 'find'], $id);
        }
        if (is_null($object)) {
            throw new AppException(AppException::ERR_OBJECT_NOT_FOUND);
        }

        $data = $this->crudProvider->onBeforeObjectUpdate($object, $data);

        DB::transaction(
            function () use ($request, $data, $object, $input_data) {
                if (!$this->crudProvider->onBeforeAccess($object)) {
                    throw new AppException(AppException::ERR_OBJ_ACCESS_DENIED);
                }

                if (is_null($this->crudStorage)) {
                    $object->update($data);
                    self::syncRelations('update', $this->crudProvider->getAutoSyncRelations(), $object, $data);

                    $this->crudProvider->onAfterUpdate($object, $input_data);
                } else {
                    $this->crudStorage->update($request, $object, $data);
                }
            }
        );

        $with = $this->crudProvider->getEagerRelations();
        if (!is_null($with)) {
            $object->load($with);
        }

        event(new CRUDEvent\CRUDUpdated($object, get_class($this->crudProvider), Carbon::now()));

        return $object;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     * @throws \Larapress\Core\Exceptions\AppException
     */
    public function destroy(Request $request, $id)
    {
        /**
         * @var Builder
         */
        $query = call_user_func([$this->crudProvider->getModelClass(), 'query']);
        $cascades = $this->crudProvider->getDeleteCascades();

        $object = $query->find($id);
        if (is_null($object)) {
            throw new AppException(AppException::ERR_OBJECT_NOT_FOUND);
        }

        $object->load($cascades);

        DB::transaction(
            function () use ($object, $cascades) {
                if (!$this->crudProvider->onBeforeAccess($object)) {
                    throw new AppException(AppException::ERR_OBJ_ACCESS_DENIED);
                }

                if ($this->crudProvider->onBeforeDestroy($object)) {
                    $object->delete();
                    // @todo: implement case cade delete
                    //                if ($this->crudProvider->onBeforeDestroyCascades($object)) {
                    //                    foreach ($cascades as $cascade) {
                    //                        $ids = collect([$object])->pluck($cascade);
                    //                    }
                    //                    $this->crudProvider->onAfterDestroyCascades($object);
                    //                }
                    //                $this->crudProvider->onAfterDestroy($object);
                }
            }
        );

        event(new CRUDEvent\CRUDDeleted($object, class_basename($this->crudProvider), Carbon::now()));

        return response()->json($object);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Larapress\Core\Exceptions\AppException
     */
    public function reports(Request $request)
    {
        $user = Auth::user();
        /** @var IReportSource[] */
        $reports = $this->crudProvider->getReportSources();

        $names = [];
        foreach ($reports as $source) {
            $sNames = $source->getReportNames($user);
            foreach ($sNames as $name) {
                $names[$name] = $source;
            }
        }

        $validate = Validator::make($request->all('name'), [
            'name' => 'required|in:' . implode(',', array_keys($names))
        ]);
        if ($validate->fails()) {
            throw new ValidationException($validate);
        }

        $report = $request->get('name');

        return $names[$report]->getReport($user, $report, $request->all());
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Larapress\Core\Exceptions\AppException
     */
    public function export(Request $request)
    {
        $query = $this->getQueryForRequest($request);
        return $this->crudExporter->getResponseForQueryExport($request, $query, $this->crudProvider);
    }

    /**
     * @param array $query_params
     *
     * @return Builder
     * @throws AppException
     */
    protected function getQueryFromRequest($query_params)
    {
        /*** @var Builder $query */
        $query = $this->crudProvider->onBeforeQuery(call_user_func([$this->crudProvider->getModelClass(), 'query']));

        if (isset($query_params['with'])) {
            foreach ($query_params['with'] as $relation) {
                if (isset($relation['name']) && isset($relation['columns'])) {
                    $name = $relation['name'];
                    $relation_columns = $relation['columns'];
                    if (in_array($name, $this->crudProvider->getValidRelations())) {
                        if (is_string($relation_columns)) {
                            $relation_columns = explode(',', $relation_columns);
                        }
                        $query->with([$name => function ($q) use ($relation_columns) {
                            if (count($relation_columns) > 0) {
                                $q->select($relation_columns);
                            }
                        }]);
                    } else {
                        throw new AppException(AppException::ERR_INVALID_QUERY);
                    }
                }
            }
        }

        if (isset($query_params['sort'])) {
            foreach ($query_params['sort'] as $sort) {
                if (isset($sort['column']) && isset($sort['direction'])) {
                    if (in_array($sort['column'], $this->crudProvider->getValidSortColumns())) {
                        $order = $sort['direction'] === 'asc' ? 'ASC' : 'DESC';
                        $query->orderBy($sort['column'], $order);
                    } else {
                        throw new AppException(AppException::ERR_INVALID_QUERY);
                    }
                }
            }
        }

        if (isset($query_params['page'])) {
            $paginate_from = $query_params['page'];
            Paginator::currentPageResolver(
                function () use ($paginate_from) {
                    return $paginate_from;
                }
            );
        }

        if (isset($query_params['search']) && strlen($query_params['search']) > 2) {
            $query->where(
                function (Builder $query) use ($query_params) {
                    $sColumns = $this->crudProvider->getSearchableColumns();
                    if (count($sColumns) > 0) {
                        foreach ($sColumns as $column) {
                            $parts = explode(':', $column);
                            if (count($parts) == 1) {
                                $query->orWhere($column, 'LIKE', '%' . $query_params['search'] . '%');
                            } else {
                                switch ($parts[0]) {
                                    case 'has':
                                        $has = explode(',', $parts[1]);
                                        if (count($has) == 2) {
                                            $query->orWhereHas(
                                                $has[0],
                                                function (Builder $q) use ($query_params, $has) {
                                                    $q->where($has[1], 'LIKE', '%' . $query_params['search'] . '%');
                                                }
                                            );
                                        }
                                        break;
                                    case 'has_exact':
                                        $has = explode(',', $parts[1]);
                                        if (count($has) == 2) {
                                            $query->orWhereHas(
                                                $has[0],
                                                function (Builder $q) use ($query_params, $has) {
                                                    $q->where($has[1], 'LIKE', $query_params['search']);
                                                }
                                            );
                                        }
                                        break;
                                    case 'equals':
                                        $query->orWhere($parts[1], '=', $query_params['search']);
                                        break;
                                }
                            }
                        }
                    }
                }
            );
        }
        if (isset($query_params['filters'])) {
            $filters = $query_params['filters'];
            $availableFilters = $this->crudProvider->getFilterFields();
            foreach ($availableFilters as $field => $options) {
                if (isset($filters[$field]) && !is_null($filters[$field])) {
                    if (is_callable($options)) {
                        $options($query, $filters[$field]);
                        continue;
                    }

                    $parts = explode(':', $options);
                    switch ($parts[0]) {
                        case 'after':
                            $query->whereDate($parts[1], '>=', $filters[$field]);
                            break;
                        case 'before':
                            $query->whereDate($parts[1], '<=', $filters[$field]);
                            break;
                        case 'has':
                            $values = $filters[$field];
                            if (is_array($values) && isset($values[0][isset($parts[3]) ? $parts[3] : 'id'])) {
                                $values = collect($values)->pluck('id')->toArray();
                            } else {
                                $values = array_keys($values);
                            }
                            if (count($values) > 0) {
                                $query->whereHas(
                                    $parts[1],
                                    function (Builder $q) use ($values, $parts) {
                                        $q->whereIn(isset($parts[2]) ? $parts[2] : 'id', $values);
                                    }
                                );
                            }
                            break;
                        case 'equals':
                            $query->where($parts[1], '=', $filters[$field]);
                            break;
                        case 'like':
                            if (strlen($filters[$field]) > 3) {
                                $query->where($parts[1], 'LIKE', '%' . $filters[$field] . '%');
                            }
                            break;
                        case 'in':
                            if (is_array($filters[$field]) && count($filters[$field]) > 0) {
                                $ins = array_keys($filters[$field]);
                                $query->whereIn($parts[1], $ins);
                            }
                            break;
                    }
                }
            }
        }

        return $query;
    }

    /**
     * @param $params
     * @param LengthAwarePaginator $paginate
     *
     * @return array
     */
    public static function formatPaginatedResponse($params, $paginate)
    {
        return [
            'data' => $paginate->items(),
            'total' => $paginate->total(),
            'from' => $paginate->firstItem(),
            'to' => $paginate->lastItem(),
            'current_page' => $paginate->currentPage(),
            'per_page' => $paginate->perPage(),
            'ref_id' => isset($params['ref_id']) ? $params['ref_id'] : null,
        ];
    }

    protected static function syncRelation($relation, $callback, $object, $data)
    {
        $saveAfter = false;
        /** @var HasMany|BelongsToMany|BelongsTo $builder */
        $builder = call_user_func([$object, $relation]);
        $builderClass = class_basename($builder);

        switch ($builderClass) {
            case class_basename(HasMany::class):
                $builder->saveMany($callback($object, $data));
                break;
            case class_basename(BelongsTo::class):
                $builder->associate($callback($object, $data));
                $saveAfter = true;
                break;
            case class_basename(BelongsToMany::class):
                $rel = $callback($object, $data);
                if (is_array($rel)) {
                    $builder->attach($rel[0], isset($rel[1]) ? $rel[1] : []);
                } else {
                    $builder->attach($rel);
                }
                break;
        }

        return $saveAfter;
    }

    /**
     * @param $method
     * @param array $autoSyncRelations
     * @param \Illuminate\Database\Eloquent\Model $object
     * @param array $data
     *
     */
    protected static function syncRelations($method, $autoSyncRelations, $object, $data)
    {
        $saveAfter = false;
        foreach ($autoSyncRelations as $relation => $callback) {
            if (is_callable($callback)) {
                $saveAfter = $saveAfter || self::syncRelation($relation, $callback, $object, $data);
            } else if (isset($callback[$method]) && is_callable($callback[$method])) {
                $saveAfter = $saveAfter || self::syncRelation($relation, $callback[$method], $object, $data);
            }
        }

        if ($saveAfter) {
            $object->save();
        }
    }
}
