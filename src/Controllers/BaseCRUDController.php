<?php

namespace Larapress\CRUD\Controllers;

use http\Env\Response;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Larapress\Core\Exceptions\AppException;
use Larapress\Core\Exceptions\ValidationException;
use Larapress\CRUD\Base\ICRUDExporter;
use Larapress\CRUD\Base\ICRUDFilterStorage;
use Larapress\CRUD\Base\ICRUDProvider;
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
     * @param \Illuminate\Http\Request $request
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(ICRUDService $service, Request $request)
    {
        $this->crudService = $service;
        $this->crudService->useCRUDFilterStorage(app()->make(ICRUDFilterStorage::class));
        $this->crudService->useCRUDExporter(app()->make(ICRUDExporter::class));

        $providerClass = $request->route()->getAction('provider');
        /** @var ICRUDProvider $provider */
        $provider = new $providerClass();
        $this->crudService->useProvider($provider);
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

    /**
     * @param string $name
     * @param string $controller
     * @param string $provider
     */
    public static function registerCrudRoutes($name, $controller, $provider)
    {
        if (! Str::startsWith($controller, '\\')) {
            $controller = '\\'.$controller;
        }

        $verbs = [
            'index' => [
                'methods' => ['GET'],
                'url' => $name,
                'uses' => $controller.'@index',
            ],
            'store' => [
                'methods' => ['POST'],
                'url' => $name,
                'uses' => $controller.'@store',
            ],
            'update' => [
                'methods' => ['PUT'],
                'url' => $name.'/{id}',
                'uses' => $controller.'@update',
            ],
            'destroy' => [
                'methods' => ['DELETE'],
                'url' => $name.'/{id}',
                'uses' => $controller.'@destroy',
            ],
            'query' => [
                'methods' => ['POST'],
                'url' => $name.'/query',
                'uses' => $controller.'@query',
            ],
            'export' => [
                'methods' => ['POST'],
                'url' => $name.'/export',
                'uses' => $controller.'@export',
            ],
        ];

        self::registerCRUDVerbs($name, $verbs, $provider);
    }

    /**
     * @param string $name
     * @param array $verbs
     * @param string $provider
     */
    public static function registerCRUDVerbs($name, $verbs, $provider)
    {
        foreach ($verbs as $verb => $data) {
            Route::match($data['methods'], $data['url'], [
                    'uses' => $data['uses'],
                    'provider' => $provider
                ])
                ->name($name.'.'.$verb);
        }
    }
}
