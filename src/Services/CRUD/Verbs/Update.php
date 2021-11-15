<?php

namespace Larapress\CRUD\Services\CRUD\Verbs;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Larapress\CRUD\Events\CRUDUpdated;
use Larapress\CRUD\Exceptions\AppException;
use Larapress\CRUD\Exceptions\ValidationException;
use Larapress\CRUD\Services\CRUD\ICRUDService;
use Larapress\CRUD\Services\CRUD\ICRUDVerb;

class Update implements ICRUDVerb {
    /**
     * Undocumented function
     *
     * @return string
     */
    public function getVerbName(): string
    {
        return ICRUDVerb::EDIT;
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
        $crudStorage = $service->getStorage();
        $id = $args[0];

        $updateRules = $crudProvider->getUpdateRules($request);

        $input_data = null;
        if ($crudProvider->shouldFilterRequestParamsByRules()) {
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

        $object = $crudStorage->update(
            $crudProvider,
            $id,
            $input_data
        );
        if (is_null($object)) {
            throw new AppException(AppException::ERR_OBJECT_NOT_FOUND);
        }
        $with = $crudProvider->getDefaultShowRelations();
        if (!is_null($with)) {
            $object->load($with);
        }

        CRUDUpdated::dispatch(Auth::user(), $object, $service->getProviderSourceClass(), Carbon::now());

        return $object;
    }
}
