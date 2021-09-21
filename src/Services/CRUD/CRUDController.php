<?php

namespace Larapress\CRUD\Services\CRUD;

use Exception;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;
use Larapress\Core\Exceptions\AppException;
use Larapress\Core\Exceptions\ValidationException;
use Larapress\CRUD\Services\CRUD\ICRUDExporter;
use Larapress\CRUD\Services\CRUD\ICRUDProvider;
use Larapress\CRUD\Services\CRUD\ICRUDService;

/**
 * Used by any resource that needs CRUD end points.
 *
 * Class CRUDController
 */
class CRUDController extends Controller
{
    protected $crudService;

    /**
     * CRUDController constructor.
     * extend the constructor and call $service->useProvide() to set your crud resource.
     *
     * @param ICRUDService $service
     * @param \Larapress\CRUD\Services\CRUD\ICRUDExporter $crudExporter
     * @param \Illuminate\Http\Request $request
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(ICRUDService $service, ICRUDExporter $crudExporter, Request $request)
    {
        $this->crudService = $service;
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
     * Query/Search
     *
     * @param Request $request
     *
     * @bodyParam limit int Limit number of records. Example: 30
     * @bodyParam page int Number of pages of records to skip. Example: 1
     * @bodyParam ref_id string A string retured from request body to keep track of request orders. Example: 1
     * @bodyParam search string A string with length of 3 or more to search in searchable columns. Example: search term
     * @bodyParam sort object[] An array of columns to sort on
     * @bodyParam sort[].column string required the columns to sort on. Example: id
     * @bodyParam sort[].direction string required sort in asc/desc direction. Example: asc
     * @bodyParam filters object An object of parameters to filter on query
     * @bodyParam appends string[] An array of attributes to append
     * @bodyParam with object[] An array of relations to include
     * @bodyParam with[].name string required relation name to include. Example: author
     * @bodyParam with[].columns string relation columns to include. Example: id,name,data
     *
     * @return Response
     *
     * @throws AppException
     * @throws \Exception
     */
    public function query(Request $request)
    {
        return $this->crudService->handle(ICRUDVerb::VIEW, $request);
    }

    /**
     * Create
     *
     * @param  Request $request
     *
     * @return \Illuminate\Http\Response
     *
     * @throws ValidationException
     * @throws AppException
     * @throws \Exception
     */
    public function store(Request $request)
    {
        $object = $this->crudService->handle(ICRUDVerb::CREATE, $request);
        return response()->json([
            'message' => trans('larapress::crud.create_success', ['id' => $object->id]),
            'object' => $object,
        ]);
    }

    /**
     * Show Details
     *
     * @param \Illuminate\Http\Request $request
     * @urlParam id int required The id of the resource to show details of
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        return response()->json($this->crudService->handle(ICRUDVerb::SHOW, $request, $id));
    }

    /**
     * Update
     *
     * @param  \Illuminate\Http\Request $request
     * @urlParam id int required The id of the resource to update
     *
     * @return \Illuminate\Http\Response
     * @throws ValidationException
     * @throws AppException
     * @throws \Exception
     */
    public function update(Request $request, $id)
    {
        $object = $this->crudService->handle(ICRUDVerb::EDIT, $request, $id);
        return response()->json([
            'message' => trans('larapress::crud.create_success', ['id' => $object->id]),
            'object' => $object,
        ]);
    }

    /**
     * Remove
     *
     * @param \Illuminate\Http\Request $request
     * @urlParam id int required The id of the resource to remove.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $object = $this->crudService->handle(ICRUDVerb::DELETE, $request, $id);
        return response()->json([
            'message' => trans('larapress::crud.remove_success', ['id' => $id]),
            'object' => $object
        ]);
    }


    /**
     * Reports
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function reports(Request $request)
    {
        return $this->crudService->handle(ICRUDVerb::REPORTS, $request);
    }

    /**
     * Export
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        return $this->crudService->handle(ICRUDVerb::EXPORT, $request);
    }

    /**
     * @param ICRUDProvider $provider
     */
    public static function registerCrudRoutes($provider)
    {
        $controller = '\\'.self::class;
        $name = $provider->getPermissionObjectName();

        $internalVerbs = [
            ICRUDVerb::CREATE => [
                'methods' => ['POST'],
                'url' => $name,
                'uses' => $controller.'@store',
            ],
            ICRUDVerb::DELETE => [
                'methods' => ['DELETE'],
                'url' => $name.'/{id}',
                'uses' => $controller.'@destroy',
            ],
            ICRUDVerb::EDIT => [
                'methods' => ['PUT'],
                'url' => $name.'/{id}',
                'uses' => $controller.'@update',
            ],
            ICRUDVerb::VIEW => [
                'methods' => ['POST'],
                'url' => $name.'/query',
                'uses' => $controller.'@query',
            ],
            ICRUDVerb::SHOW => [
                'methods' => ['GET'],
                'url' => $name.'/{id}',
                'uses' => $controller.'@show',
            ],
            ICRUDVerb::EXPORT => [
                'methods' => ['POST'],
                'url' => $name.'/export',
                'uses' => $controller.'@export',
            ],
            ICRUDVerb::REPORTS => [
                'methods' => ['POST'],
                'url' => $name.'/reports',
                'uses' => $controller.'@reports',
            ]
        ];

        $avVerbs = $provider->getPermissionVerbs();
        $verbs = [];

        foreach ($avVerbs as $verb => $meta) {
            if (is_string($meta) && isset($internalVerbs[$meta])) {
                $verbs[$meta] = $internalVerbs[$meta];
            } else if (is_string($verb) && is_array($meta)) {
                if (isset($meta['uses']) && isset($meta['url']) && isset($meta['methods'])) {
                    $vervs[$verb] = $meta;
                } else {
                    throw new Exception("Invalid CRUD verb. $provider->getPermissionObjectName() for verb $verb");
                }
            }
        }

        self::registerVerbs($name, $verbs, get_class($provider));
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
