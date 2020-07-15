<?php

namespace Larapress\CRUD\CRUDControllers;

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
use Larapress\CRUD\Base\IPermissionsMetadata;

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
     * @param \Larapress\CRUD\Base\ICRUDFilterStorage $filterStorage
     * @param \Larapress\CRUD\Base\ICRUDExporter $crudExporter
     * @param \Illuminate\Http\Request $request
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(ICRUDService $service, ICRUDFilterStorage $filterStorage, ICRUDExporter $crudExporter, Request $request)
    {
        $this->crudService = $service;
        $this->crudService->useCRUDFilterStorage($filterStorage);
        $this->crudService->useCRUDExporter($crudExporter);

        if (! is_null($request->route())) {
            $providerClass = $request->route()->getAction('provider');

            if (class_exists($providerClass)) {
                /** @var ICRUDProvider $provider */
                $provider = new $providerClass();
                $this->crudService->useProvider($provider);
            }
        }
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


    /**
     * Remove the specified resource from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function reports(Request $request)
    {
        return $this->crudService->reports($request);
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        return $this->crudService->export($request);
    }

    /**
     * @param string $name
     * @param string $controller
     * @param string $provider
     */
    public static function registerCrudRoutes($name, $controller, $provider, $additionalVerbs = [])
    {
        if (! Str::startsWith($controller, '\\')) {
            $controller = '\\'.$controller;
        }

        /** @var IPermissionsMetadata */
        $pro = new $provider();
        $avVerbs = $pro->getPermissionVerbs();
        $verbs = $additionalVerbs;

        if (in_array(IPermissionsMetadata::CREATE, $avVerbs)) {
            $verbs['store'] = [
                'methods' => ['POST'],
                'url' => $name,
                'uses' => $controller.'@store',
            ];
        }
        if (in_array(IPermissionsMetadata::DELETE, $avVerbs)) {
            $verbs['desroy'] = [
                'methods' => ['DELETE'],
                'url' => $name.'/{id}',
                'uses' => $controller.'@destroy',
            ];
        }
        if (in_array(IPermissionsMetadata::EDIT, $avVerbs)) {
            $verbs['update'] = [
                'methods' => ['PUT'],
                'url' => $name.'/{id}',
                'uses' => $controller.'@update',
            ];
        }
        if (in_array(IPermissionsMetadata::VIEW, $avVerbs)) {
            $verbs['query'] = [
                'methods' => ['POST'],
                'url' => $name.'/query',
                'uses' => $controller.'@query',
            ];
            $verbs['export'] = [
                'methods' => ['POST'],
                'url' => $name.'/export',
                'uses' => $controller.'@export',
            ];
        }
        if (in_array(IPermissionsMetadata::REPORTS, $avVerbs)) {
            $verbs['reports'] = [
                'methods' => ['POST'],
                'url' => $name.'/reports',
                'uses' => $controller.'@reports',
            ];
        }

        self::registerVerbs($name, $verbs, $provider);
    }

    /**
     * @param string $name
     * @param array $verbs
     * @param string $provider
     */
    public static function registerVerbs($name, $verbs, $provider)
    {
        foreach ($verbs as $verb => $data) {
            Route::match($data['methods'], $data['url'], [
                    'uses' => $data['uses'],
                    'provider' => $provider,
                ])
                ->name($name.'.'.$verb);
        }
    }
}
