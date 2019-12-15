<?php

namespace Larapress\CRUD\Controllers;

use http\Env\Response;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;
use Larapress\Core\Exceptions\AppException;
use Larapress\Core\Exceptions\ValidationException;
use Larapress\CRUD\Base\ICRUDExporter;
use Larapress\CRUD\Base\ICRUDFilterStorage;
use Larapress\CRUD\Base\ICRUDService;

/**
 * Used by any resource that needs CRUD end points.
 *
 * Class BaseCRUDController
 */
abstract class BaseCRUDController extends Controller
{
    protected $crudService;

    /**
     * BaseCRUDController constructor.
     * extend the constructor and call $service->useProvide() to set your crud resource.
     *
     * @param ICRUDService $service
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(ICRUDService $service)
    {
        $this->crudService = $service;
        $this->crudService->useCRUDFilterStorage(app()->make(ICRUDFilterStorage::class));
        $this->crudService->useCRUDExporter(app()->make(ICRUDExporter::class));
    }

    /**
     * Search the resource.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     * @throws AppException
     * @throws \Exception
     */
    public function query(Request $request)
    {
        return $this->crudService->query($request);
    }

    /**
     * filter the searching resources.
     *
     * @param Request $request
     *
     * @return LengthAwarePaginator
     * @throws AppException
     * @throws \Exception
     */
    public function filter(Request $request)
    {
        return $this->crudService->filter($request);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->crudService->index($request);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     *
     * @return Response
     * @throws ValidationException
     * @throws AppException
     * @throws \Exception
     */
    public function store(Request $request)
    {
        return response()->json($this->crudService->store($request));
    }

    /**
     * Display the specified resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int $id
     *
     * @return Response
     */
    public function show(Request $request, $id)
    {
        return response()->json($this->crudService->show($request, $id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     *
     * @return \Illuminate\Http\Response
     * @throws ValidationException
     * @throws AppException
     * @throws \Exception
     */
    public function update(Request $request, $id)
    {
        return $this->crudService->update($request, $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        return $this->crudService->destroy($request, $id);
    }

    public function export(Request $request)
    {
        return $this->crudService->export($request);
    }

    public static function routes($name, $controller, $destroys = true)
    {
        if (is_string($controller)) {
            $controller = '\\'.$controller;
        }

        Route::post($name.'/query', $controller.'@query')->name($name.'.query');
        Route::post($name.'/export', $controller.'@export')->name($name.'.query.export');
        $api = Route::apiResource($name, $controller);
        if (! $destroys) {
            $api->except('destroy');
        }
        $api->register();
    }
}
