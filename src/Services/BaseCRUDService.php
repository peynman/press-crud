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
use Larapress\CRUD\Events\CRUDCreated;
use Larapress\CRUD\Events\CRUDDeleted;
use Larapress\CRUD\Events\CRUDUpdated;
use Larapress\CRUD\Extend\Helpers;

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
    public function useCRUDStorage(ICRUDStorage $storage)
    {
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
        [$query, $total] = $this->buildQueryForRequest($request);
        $models = $query->get();
        if ($total === -1) {
            $total = $models->count();
        }

        $appends = $request->get('appends', []);
        if (isset($appends)) {
            foreach ($appends as $append) {
                if (isset($append['attribute'])) {
                    $models->makeVisible($append['attribute']);
                }
            }
        }

        return [
            'data' => $models,
            'total' => $total,
            'from' => ($request->get('page', 1) - 1) * $request->get('limit', 10),
            'to' => $request->get('page', 1) * $request->get('limit', 10),
            'current_page' => $request->get('page', 0),
            'per_page' => $request->get('limit', 10),
            'ref_id' => $request->get('ref_id'),
        ];
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @return Builder
     * @throws AppException
     * @throws \Exception
     */
    public function buildQueryForRequest(Request $request, $onBeforeQuery = null)
    {
        $query_string = $request->getContent();
        $query_params = json_decode($query_string, true);
        if (is_null($query_params)) {
            $query_params = [];
        }

        return $this->getQueryFromRequest($query_params, $onBeforeQuery);
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

        CRUDCreated::dispatch(Auth::user(), $object, get_class($this->crudProvider), Carbon::now());

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

        CRUDUpdated::dispatch(Auth::user(), $object, get_class($this->crudProvider), Carbon::now());

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
                    $this->crudProvider->onAfterDestroy($object);
                }
            }
        );

        CRUDDeleted::dispatch(Auth::user(), $object, get_class($this->crudProvider), Carbon::now());

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
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '1G');

        [$query, $total] = $this->buildQueryForRequest($request);
        return $this->crudExporter->getResponseForQueryExport($request, $query, $this->crudProvider);
    }

    /**
     * @param array $query_params
     *
     * @return Builder
     * @throws AppException
     */
    protected function getQueryFromRequest($query_params, $onBeforeQuery = null)
    {
        /** @var IProfileUser */
        $user = Auth::user();
        /*** @var Builder $query */
        $query = $this->crudProvider->onBeforeQuery(call_user_func([$this->crudProvider->getModelClass(), 'query']));
        if (!is_null($onBeforeQuery)) {
            $onBeforeQuery($query);
        }

        if (isset($query_params['with'])) {
            foreach ($query_params['with'] as $relation) {
                if (isset($relation['name']) && isset($relation['columns'])) {
                    $name = $relation['name'];
                    $relation_columns = $relation['columns'];
                    $validations = $this->crudProvider->getValidRelations();
                    if (Helpers::isAssocArray($validations)) {
                        $validationNames = array_keys($validations);
                    } else {
                        $validationNames = array_values($validations);
                    }
                    if (in_array($name, $validationNames)) {
                        if (is_string($relation_columns)) {
                            $relation_columns = array_map(function($item){
                                return trim($item);
                            }, explode(',', $relation_columns));
                        }
                        $shouldInclude = true;
                        if (isset($validations[$name]) && is_callable($validations[$name])) {
                            $shouldInclude = $validations[$name]($user);
                        }
                        if ($shouldInclude) {
                            $query->with([$name => function ($q) use ($relation_columns) {
                                if (count($relation_columns) > 0 && $relation_columns[0] !== "*") {
                                    $q->select($relation_columns);
                                }
                            }]);
                        }
                    } else {
                        throw new AppException(AppException::ERR_INVALID_QUERY);
                    }
                }
            }
        }

        $hasFilters = false;
        if (isset($query_params['filters'])) {
            $filters = $query_params['filters'];
            $availableFilters = $this->crudProvider->getFilterFields();
            foreach ($availableFilters as $field => $options) {
                if (isset($filters[$field]) && !is_null($filters[$field]) && (!is_array($filters[$field]) || count(array_keys($filters[$field])) > 0)) {
                    $hasFilters = true;
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
                                if (is_array($values)) {
                                    $values = array_keys($values);
                                } else {
                                    $values = [$values];
                                }
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
                        case 'has-has':
                            $values = $filters[$field];
                            if (is_array($values) && isset($values[0][isset($parts[3]) ? $parts[3] : 'id'])) {
                                $values = collect($values)->pluck('id')->toArray();
                            } else {
                                if (is_array($values)) {
                                    $values = array_values($values);
                                } else {
                                    $values = [$values];
                                }
                            }
                            if (count($values) > 0) {
                                $query->whereHas(
                                    $parts[1],
                                    function (Builder $q) use ($values, $parts) {
                                        $q->whereHas($parts[2], function ($q) use ($parts, $values) {
                                            $q->whereIn($parts[3], $values);
                                        });
                                    }
                                );
                            }
                            break;
                        case 'equals':
                            $query->where($parts[1], '=', $filters[$field]);
                            break;
                        case 'bitwise':
                            $query->where($parts[1], '&', $filters[$field]);
                            break;
                        case 'like':
                            if (strlen($filters[$field]) > 3) {
                                $query->where($parts[1], 'LIKE', $filters[$field]);
                            }
                            break;
                        case 'in':
                            if (is_array($filters[$field]) && count($filters[$field]) > 0) {
                                $ins = array_keys($filters[$field]);
                                $query->whereIn($parts[1], $ins);
                            }
                            break;
                        case 'has-count':
                            if (is_numeric($filters[$field])) {
                                $query->withCount($parts[1])->where($parts[1].'_count', $parts[2], $filters[$field]);
                            }
                            break;
                        case '>':
                        case '<':
                        case '>=':
                        case '>=':
                            if (is_numeric($filters[$field])) {
                                $query->where($parts[1], $parts[0], $filters[$field]);
                            }
                            break;
                    }
                }
            }
        }

        if (isset($query_params['search']) && strlen($query_params['search']) >= 2 && !$hasFilters) {
            if (Str::startsWith($query_params['search'], '#')) {
                $query->where('id', substr($query_params['search'], 1));
            } else {
                $sColumns = $this->crudProvider->getSearchableColumns();
                $searchIndexer = 0;
                if (count($sColumns) > 0) {
                    foreach ($sColumns as $column) {
                        $parts = explode(':', $column);
                        // dont clone and union the query for last search column
                        if ($searchIndexer === count($sColumns) - 1) {
                            $search = $query;
                        } else {
                            $search = clone $query;
                        }
                        $searchIndexer++;
                        if (count($parts) == 1) {
                            $search->where($column, 'LIKE', '%' . $query_params['search'] . '%');
                        } else {
                            switch ($parts[0]) {
                                case 'has':
                                    $has = explode(',', $parts[1]);
                                    if (count($has) == 2) {
                                        $search->whereHas(
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
                                        $search->whereHas(
                                            $has[0],
                                            function (Builder $q) use ($query_params, $has) {
                                                $q->where($has[1], $query_params['search']);
                                            }
                                        );
                                    }
                                    break;
                                case 'equals':
                                    $search->where($parts[1], '=', $query_params['search']);
                                    break;
                            }
                        }
                        if ($search !== $query) {
                            $query->union($search);
                        }
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

        $cq = clone $query;
        // get total items count for pagination if query is not a search
        if (!isset($query_params['search']) || strlen($query_params['search']) < 2) {
            $total = $cq->count();
        } else {
            $total = -1;
        }
        // no more pagination by laravel eloquent
        if (isset($query_params['page'])) {
            $paginate_from = intval($query_params['page']) - 1;
            // use pagination if we are not searching
            if (!isset($query_params['search']) || strlen($query_params['search']) < 2) {
                $limit = isset($query_params['limit']) ? $query_params['limit'] : 10;
                if ($total > 100) {
                    $offset = $cq->select('id')->skip($paginate_from * $limit)->first();
                    if (!is_null($offset)) {
                        $query->where('id', '<=', $offset->id);
                    }
                    $query->take($limit);
                } else {
                    $query->skip($paginate_from * $limit)->take($limit);
                }
            }
        }

        return [$query, $total];
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
