<?php

namespace Larapress\CRUD\Services\CRUD;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Larapress\CRUD\Exceptions\AppException;
use Larapress\CRUD\Exceptions\ValidationException;
use Larapress\CRUD\Events\CRUDCreated;
use Larapress\CRUD\Events\CRUDDeleted;
use Larapress\CRUD\Events\CRUDUpdated;
use Larapress\CRUD\Exceptions\RequestException;
use Larapress\CRUD\Extend\Helpers;
use Larapress\CRUD\Services\CRUD\RDBStorage;

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

    public function __construct()
    {
        $this->crudStorage = new RDBStorage();
    }

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
     * Display a specified resource by its id.
     * load default relations, check object access rights for authenticated user.
     *
     * @param Request $request
     * @param int|string     $id
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
        $object = $this->crudStorage->update(
            $this->crudProvider,
            $id,
            $input_data
        );
        if (is_null($object)) {
            throw new AppException(AppException::ERR_OBJECT_NOT_FOUND);
        }
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
            throw new RequestException(trans('larapress::exceptions.app.').AppException::ERR_OBJECT_NOT_FOUND);
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
     * Search the resource.
     *
     * @param Request $request
     *
     * @return LengthAwarePaginator
     * @throws AppException
     */
    public function query(Request $request)
    {
        [$query, $total, $summerized] = $this->buildQueryForRequest($request);
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
            'summerized' => $summerized,
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

        $object = $this->crudStorage->store($this->crudProvider, $input_data);
        $with = $this->crudProvider->getEagerRelations();
        if (!is_null($with)) {
            $object->load($with);
        }

        CRUDCreated::dispatch(Auth::user(), $object, get_class($this->crudProvider), Carbon::now());

        return $object;
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

        $availableFilters = $this->crudProvider->getFilterFields();

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
                            $relation_columns = array_map(function ($item) {
                                return trim($item);
                            }, explode(',', $relation_columns));
                        }
                        $shouldInclude = true;
                        if (isset($validations[$name]) && is_callable($validations[$name])) {
                            $shouldInclude = $validations[$name]($user);
                        }
                        if ($shouldInclude) {
                            $query->with([$name => function ($q) use ($relation_columns, $name, $query_params, $availableFilters) {
                                if (count($relation_columns) > 0 && $relation_columns[0] !== "*") {
                                    $q->select($relation_columns);
                                }
                                if (isset($availableFilters['relations'][$name])) {
                                    $this->addFiltersToQuery($q, $availableFilters['relations'][$name], $query_params);
                                }
                            }]);
                        }
                    } else {
                        throw new AppException(AppException::ERR_INVALID_QUERY);
                    }
                }
            }
        }

        if (isset($query_params['search']) && strlen($query_params['search']) >= 2) {
            self::addSearchToQuery($query, $this->crudProvider->getSearchableColumns(), $query_params);
        } else {
            $this->addFiltersToQuery($query, $availableFilters, $query_params);
        }

        if (isset($query_params['sort'])) {
            foreach ($query_params['sort'] as $sort) {
                if (isset($sort['column']) && isset($sort['direction'])) {
                    $validSorts = $this->crudProvider->getValidSortColumns();
                    $validSortNames = Helpers::isAssocArray($validSorts) ? array_keys($validSorts) : array_values($validSorts);
                    if (in_array($sort['column'], $validSortNames)) {
                        $dir = $sort['direction'] === 'asc' ? 'ASC' : 'DESC';
                        if (isset($validSorts[$sort['column']]) && is_callable($validSorts[$sort['column']])) {
                            $validSorts[$sort['column']]($query, $dir);
                        } else {
                            $query->orderBy($sort['column'], $dir);
                        }
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

        $paginate_from = 0;
        if (isset($query_params['page'])) {
            $paginate_from = max(0, intval($query_params['page']) - 1);
        }

        $summerized_column_values = [];

        // use pagination if we are not searching
        if (!isset($query_params['search']) || strlen($query_params['search']) < 2) {
            // calculate summerized columns only for first page query
            $summerize_columns = [];
            if ($paginate_from === 0) {
                if (isset($query_params['summerize'])) {
                    if (!is_array($query_params['summerize'])) {
                        $summerize_columns = [$query_params['summerize']];
                    } else {
                        $summerize_columns = $query_params['summerize'];
                    }
                }
                $validSummerize = $this->crudProvider->getSummerizableColumns();
                $validSummNames = array_keys($validSummerize);
                foreach ($summerize_columns as $summColName) {
                    if (in_array($summColName, $validSummNames)) {
                        if (is_callable($validSummerize[$summColName])) {
                            $summerized_column_values[$summColName] = $validSummerize[$summColName]($query, $query_params);
                        }
                    }
                }
            }
        }

        $limit = isset($query_params['limit']) ? $query_params['limit'] : 10;
        if ($total > 100) {
            $offset = $cq->skip($paginate_from * $limit)->first();
            if (!is_null($offset)) {
                $query->where('id', '<=', $offset->id);
            }
            $query->take($limit);
        } else {
            $query->skip($paginate_from * $limit)->take($limit);
        }

        return [$query, $total, $summerized_column_values];
    }

    public static function addSearchToQuery($query, $sColumns, $query_params)
    {
        if (isset($query_params['search']) && strlen($query_params['search']) >= 2) {
            if (Str::startsWith($query_params['search'], '#')) {
                $query->where('id', substr($query_params['search'], 1));
            } else {
                $searchIndexer = 0;
                ini_set('memory_limit', '256M');
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
    }

    /**
     * Undocumented function
     *
     * @param Builder $query
     * @param array $availableFilters
     * @param array $query_params
     * @return boolean
     */
    public function addFiltersToQuery($query, $availableFilters, $query_params)
    {
        $hasFilters = false;
        if (isset($query_params['filters'])) {
            $filters = $query_params['filters'];
            foreach ($availableFilters as $field => $options) {
                if (isset($filters[$field]) && !is_null($filters[$field]) && (!is_array($filters[$field]) || count(array_keys($filters[$field])) > 0)) {
                    $hasFilters = true;
                    if (is_callable($options)) {
                        if (!empty($filters[$field])) {
                            $options($query, $filters[$field]);
                        }
                        continue;
                    }
                    if ($field === 'flags' && $filters[$field] == 0) {
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
                        case 'hasnot-has':
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
                                $query->whereDoesntHave(
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
                                $query->withCount($parts[1])->having($parts[1] . '_count', $parts[2], $filters[$field]);
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
                        case 'not-null':
                            $query->whereNotNull($parts[1]);
                            break;
                        case 'null':
                            $query->whereIsNull($parts[1]);
                            break;
                    }
                }
            }
        }

        return $hasFilters;
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
}
