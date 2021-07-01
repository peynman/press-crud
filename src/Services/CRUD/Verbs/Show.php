<?php

namespace Larapress\CRUD\Services\CRUD\Verbs;

use Illuminate\Http\Request;
use Larapress\CRUD\Exceptions\AppException;
use Larapress\CRUD\Services\CRUD\ICRUDService;
use Larapress\CRUD\Services\CRUD\ICRUDVerb;

class Show implements ICRUDVerb {
    /**
     * Undocumented function
     *
     * @return string
     */
    public function getVerbName(): string
    {
        return 'show';
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
        $with = $crudProvider->getEagerRelations();
        if (!is_null($with)) {
            $query->with($with);
        }
        $model = $query->find($id);

        if (!$this->crudProvider->onBeforeAccess($model)) {
            throw new AppException(AppException::ERR_OBJ_ACCESS_DENIED);
        }

        return $crudProvider->onShowModel($model);
    }
}
