<?php


namespace Larapress\CRUD\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Larapress\Core\Exceptions\AppException;
use Larapress\Core\Exceptions\ValidationException;
use Larapress\CRUD\Events as CRUDEvent;

/**
 * Class BaseCRUDService
 *
 * @package Larapress\CRUD\Base
 */
class BaseCRUDService implements ICRUDService
{
    /**
     * @var ICRUDProvider
     */
    public $crudProvider;
    /**
     * @var ICRUDStorage
     */
    public $crudStorage;
    /**
     * @var ICRUDFilterStorage
     */
    public $crudFilterStorage;
    /**
     * @var ICRUDExporter
     */
    public $crudExporter;

    /**
     * @param ICRUDFilterStorage $storage
     */
    public function useCRUDFilterStorage(ICRUDFilterStorage $storage)
    {
        $this->crudFilterStorage = $storage;
    }

    /**
     * @param ICRUDProvider $provider
     */
    public function useProvider(ICRUDProvider $provider)
    {
        $this->crudProvider = $provider;
    }

    /**
     * @param ICRUDStorage $storage
     */
    public function useCRUDStorage(ICRUDStorage $storage)
    {
        $this->crudStorage = $storage;
    }

    /**
     * @param ICRUDExporter $exporter
     */
    public function useCRUDExporter(ICRUDExporter $exporter)
    {
        $this->crudExporter = $exporter;
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
        $query_string = $request->getContent();
        $query_params = json_decode($query_string, true);
        if (is_null($query_params)) {
            $query_params = [];
        }

        $query = $this->getQueryFromRequest($query_params);
        $models = $query->paginate(isset($query_params['limit']) ? $query_params['limit']:10);
        event(new CRUDEvent\CRUDQueried($models->items(), Carbon::now()));

        return self::formatPaginatedResponse($query_params, $models);
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function filter(Request $request)
    {
        /**
    * @var Builder $query
*/
        $userId = auth()->guest() ? null: auth()->user()->id;
        $filterKey = self::getFilterKey($request->getSession()->getId(), class_basename($this->crudProvider));
        if ($request->get('remove-filter') == true) {
            $this->crudFilterStorage->putFilters($filterKey, null, $userId);
            return [];
        }

        $recordFilters = [];
        $availableOptions = $this->crudProvider->getFilterFields();
        foreach ($availableOptions as $field => $options) {
            if (!is_null($request->get($field))) {
                $value = $request->get($field);
                if (!is_null($value)) {
                    $recordFilters[$field] = $value;
                }
            }
        }
        $this->crudFilterStorage->putFilters($filterKey, $recordFilters, $userId);

        return $recordFilters;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     * @return LengthAwarePaginator
     */
    public function index(Request $request)
    {
        $query = call_user_func([$this->crudProvider->getModelClass(), 'query']);
        $query = $this->crudProvider->onBeforeQuery($query);
        $models = $query->paginate(100);
        event(new CRUDEvent\CRUDQueried($models->items(), Carbon::now()));

        return self::formatPaginatedResponse(
            $request->all(),
            $models
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     * @throws \Larapress\Core\Exceptions\ValidationException
     * @throws AppException
     * @throws \Exception
     */
    public function store(Request $request)
    {
        $createRules = $this->crudProvider->getCreateRules();
        $translations = $this->crudProvider->getTranslations();
        $json_data = $this->crudProvider->getJSONFills();

        $translation_rules = [];
        if ($this->crudProvider->shouldFilterRequestParamsByRules()) {
            $keys = array_keys($createRules);
            foreach ($json_data as $prefix => $items) {
                foreach ($items as $item) {
                    $keys[] = $prefix.'_'.$item;
                }
            }
            foreach ($translations as $key => $rule) {
                $translation_rules[$key.'_translations'] = $rule;
                $keys[] = $key.'_translations';
            }
            $input_data = $request->all($keys);
        } else {
            $input_data = $request->all();
        }

        $validate = Validator::make($input_data, $createRules);
        if ($validate->fails()) {
            throw new ValidationException($validate);
        }

        $data = $this->crudProvider->onBeforeCreate($input_data);

        $json_values = [];
        foreach ($json_data as $prefix => $items) {
            $json_values[$prefix] = [];
            foreach ($items as $item) {
                if (!empty($data[$prefix.'_'.$item])) {
                    $json_values[$prefix][$item] = $data[$prefix.'_'.$item];
                }
            }
        }
        foreach ($json_values as $param => $obj) {
            if (isset($data[$param])) {
                $data[$param] = array_merge($data[$param], $obj);
            } else {
                $data[$param] = $obj;
            }
        }

        $translation_fields = array_keys($translations);
        $translations_object = [];
        foreach ($translation_fields as $field) {
            $translations_object[$field] = isset($input_data[$field.'_translations']) ?
                json_decode($input_data[$field.'_translations']):null;
        }
        $input_data['translations'] = $translations_object;

        $json_values = [];
        foreach ($json_data as $prefix => $items) {
            $json_values[$prefix] = [];
            foreach ($items as $item) {
                if (!empty($data[$prefix.'_'.$item])) {
                    $json_values[$prefix][$item] = $data[$prefix.'_'.$item];
                }
            }
        }
        foreach ($json_values as $param => $obj) {
            if (isset($data[$param])) {
                $data[$param] = array_merge($data[$param], $obj);
            } else {
                $data[$param] = $obj;
            }
        }

        try {
            DB::beginTransaction();
            if (is_null($this->crudStorage)) {
                $object = call_user_func([$this->crudProvider->getModelClass(), 'create'], $data);
                self::syncRelations($this->crudProvider->getAutoSyncRelations(), $object, $data);
            } else {
                $object = $this->crudStorage->store($request, $data);
            }

            $this->crudProvider->onAfterCreate($object, $input_data);
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        event(new CRUDEvent\CRUDCreated($object, Carbon::now()));
        event(new CRUDEvent\RelationsEvent($this->crudProvider, $object, Carbon::now()));

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
    * @var Builder $query
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
                        $model[$prefix.'_'.$item] = $model->$prefix[$item];
                    }
                }
            }
        }

        $model = $this->crudProvider->onShowModel($model);
        event(new CRUDEvent\CRUDQueried([$model], Carbon::now()));

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
        $updateRules = $this->crudProvider->getUpdateRules();
        $translations = $this->crudProvider->getTranslations();
        $json_data = $this->crudProvider->getJSONFills();

        $translation_rules = [];

        if ($this->crudProvider->shouldFilterRequestParamsByRules()) {
            $keys = array_keys($updateRules);
            foreach ($json_data as $prefix => $items) {
                foreach ($items as $item) {
                    $keys[] = $prefix.'_'.$item;
                }
            }
            foreach ($translations as $key => $rule) {
                $translation_rules[$key.'_translations'] = $rule;
                $keys[] = $key.'_translations';
            }
            $input_data = $request->all($keys);
        } else {
            $input_data = $request->all();
        }

        $validate = Validator::make($input_data, array_merge($translation_rules, $updateRules));
        if ($validate->fails()) {
            throw new ValidationException($validate);
        }

        $input_data = array_diff($input_data, $this->crudProvider->getExcludeUpdate());
        $translation_fields = array_keys($translations);
        $translations_object = [];
        foreach ($translation_fields as $field) {
            $translations_object[$field] = isset($input_data[$field.'_translations']) ?
                json_decode($input_data[$field.'_translations']):null;
        }
        $input_data['translations'] = $translations_object;
        $data = $this->crudProvider->onBeforeUpdate($input_data);

        $json_values = [];
        foreach ($json_data as $prefix => $items) {
            $json_values[$prefix] = [];
            foreach ($items as $item) {
                if (!empty($data[$prefix.'_'.$item])) {
                    $json_values[$prefix][$item] = $data[$prefix.'_'.$item];
                }
            }
        }
        foreach ($json_values as $param => $obj) {
            if (isset($data[$param])) {
                $data[$param] = array_merge($data[$param], $obj);
            } else {
                $data[$param] = $obj;
            }
        }

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
                    self::syncRelations($this->crudProvider->getAutoSyncRelations(), $object, $data);

                    $this->crudProvider->onAfterUpdate($object, $input_data);
                } else {
                    $this->crudStorage->update($request, $object, $data);
                }
            }
        );

        event(new CRUDEvent\CRUDUpdated($object, Carbon::now()));

        return $object;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     * @throws AppException
     */
    public function destroy($id)
    {
        /**
    * @var Builder $query
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

        event(new CRUDEvent\CRUDDeleted($object, Carbon::now()));

        return response()->json($object);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Larapress\Core\Exceptions\AppException
     */
    public function export(Request $request)
    {
        $userId = auth()->guest() ? null: auth()->user()->id;
        $filterKey = self::getFilterKey($request->getSession()->getId(), class_basename($this->crudProvider));
        $filters = $this->crudFilterStorage->getFilters($filterKey, null, $userId);
        $params = [];
        if (!is_null($filters)) {
            $params = ['filters' => $filters];
        }
        $query = $this->getQueryFromRequest($params);
        return $this->crudExporter->getResponseForQueryExport($query, $this->crudProvider);
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
        $query =  $this->crudProvider->onBeforeQuery(call_user_func([$this->crudProvider->getModelClass(), 'query']));

        if (isset($query_params['with'])) {
            foreach ($query_params['with'] as $relation => $relation_columns) {
                if (in_array($relation, $this->crudProvider->getValidRelations())) {
                    $query->with($relation);
                } else {
                    throw new AppException(AppException::ERR_INVALID_QUERY);
                }
            }
        }

        if (isset($query_params['sort'])) {
            foreach ($query_params['sort'] as $sort) {
                if (isset($sort['column']) && isset($sort['direction'])) {
                    if (in_array($sort['column'], $this->crudProvider->getValidSortColumns())) {
                        $order = $sort['direction'] === 'asc' ? 'ASC':'DESC';
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

        if (isset($query_params['search'])) {
            $query->where(
                function (Builder $query) use ($query_params) {
                    $sColumns = $this->crudProvider->getSearchableColumns();
                    if (count($sColumns) > 0) {
                        foreach ($sColumns as $column) {
                             $parts = explode(':', $column);
                            if (count($parts) == 1) {
                                $query->orWhere($column, 'LIKE', '%'.$query_params['search'].'%');
                            } else {
                                switch ($parts[0]) {
                                    case 'has':
                                        $has = explode(',', $parts[1]);
                                        if (count($has) == 2) {
                                            $query->orWhereHas(
                                                $has[0],
                                                function (Builder $q) use ($query_params, $has) {
                                                      $q->where(
                                                          $has[1],
                                                          'LIKE',
                                                          '%'.$query_params['search'].'%'
                                                      );
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
                            if (!is_array($values)) {
                                $values = [$values];
                            }
                            $query->whereHas(
                                $parts[1],
                                function (Builder $q) use ($values, $parts) {
                                    $q->whereIn(isset($parts[2])? $parts[2]:'id', $values);
                                }
                            );
                            break;
                        case 'equals':
                               $query->where($parts[1], '=', $filters[$field]);
                            break;
                        case 'like':
                            $query->where($parts[1], 'LIKE', '%'.$filters[$field].'%');
                            break;
                    }
                }
            }
        }

        return $query;
    }

    /**
     * @param $sessionId
     * @param $class
     *
     * @return string
     */
    public static function getFilterKey($sessionId, $class)
    {
        return 'filters.'.$class.'.'.$sessionId;
    }

    /**
     * @param $params
     * @param LengthAwarePaginator $paginate
     *
     * @return LengthAwarePaginator
     */
    public static function formatPaginatedResponse($params, $paginate)
    {
        return (LengthAwarePaginator::class)(
            [
            'data' => $paginate->items(),
            'total' => $paginate->total(),
            'from' => $paginate->firstItem(),
            'to' => $paginate->lastItem(),
            'current_page' => $paginate->currentPage(),
            'per_page' => $paginate->perPage(),
            'ref_id' => isset($params['ref_id']) ? $params['ref_id']:null,
            ]
        );
    }

    /**
     * @param $autoSyncRelations
     * @param $object
     * @param $data
     *
     * @throws \Exception
     */
    protected static function syncRelations($autoSyncRelations, $object, $data)
    {
        foreach ($autoSyncRelations as $relation) {
            if (isset($data[$relation])) {
                $builder = call_user_func([$object, $relation]);
                $classname = class_basename($builder);
                switch ($classname) {
                    case 'BelongsToMany':
                        /*** @var BelongsToMany $builder */
                        $builder->sync($data[$relation]);
                        break;
                    case 'BelongsTo':
                        if (is_object($data[$relation])) {
                            $data[$relation] = $data[$relation][0];
                        }
                        /*** @var BelongsTo $builder */
                        $builder->associate($data[$relation])->save();
                        break;
                    case 'HasMany':
                        /*** @var HasMany $builder */
                        $builder->delete();
                        $builder->saveMany($data[$relation]);
                        break;
                    default:
                        throw new \Exception(
                            'No relation found for classname: '.
                            $classname.' in '.class_basename(self::class)
                        );
                }
            }
        }
    }
}
