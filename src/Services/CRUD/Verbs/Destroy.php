<?php

namespace Larapress\CRUD\Services\CRUD\Verbs;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Larapress\CRUD\Events\CRUDDeleted;
use Larapress\CRUD\Exceptions\AppException;
use Larapress\CRUD\Exceptions\RequestException;
use Larapress\CRUD\Services\CRUD\ICRUDService;
use Larapress\CRUD\Services\CRUD\ICRUDVerb;

class Destroy implements ICRUDVerb
{
    /**
     * Undocumented function
     *
     * @return string
     */
    public function getVerbName(): string
    {
        return 'destroy';
    }

    /**
     * Undocumented function
     *
     * @param ICRUDService $service
     * @param Request $request
     * @param ...$args
     *
     * @return mixed
     */
    public function handle(ICRUDService $service, Request $request, ...$args)
    {
        $crudProvider = $service->getCompositeProvider();
        $id = $args[0];

        /**
         * @var Builder
         */
        $query = call_user_func([$crudProvider->getModelClass(), 'query']);

        $object = $query->withTrashed()->find($id);
        if (is_null($object)) {
            throw new RequestException(trans('larapress::exceptions.app.') . AppException::ERR_OBJECT_NOT_FOUND);
        }

        DB::transaction(
            function () use ($service, $object, $crudProvider) {
                if (!$crudProvider->onBeforeAccess($object)) {
                    throw new AppException(AppException::ERR_OBJ_ACCESS_DENIED);
                }

                if ($crudProvider->onBeforeDestroy($object)) {
                    $object->delete();
                    $crudProvider->onAfterDestroy($object);
                }

                CRUDDeleted::dispatch(Auth::user(), $object, $service->getProviderSourceClass(), Carbon::now());
            }
        );

        return $object;
    }
}
