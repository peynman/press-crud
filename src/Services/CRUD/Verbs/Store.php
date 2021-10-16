<?php

namespace Larapress\CRUD\Services\CRUD\Verbs;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Larapress\CRUD\Events\CRUDCreated;
use Larapress\CRUD\Exceptions\ValidationException;
use Larapress\CRUD\Services\CRUD\ICRUDService;
use Larapress\CRUD\Services\CRUD\ICRUDVerb;

class Store implements ICRUDVerb {
    /**
     * Undocumented function
     *
     * @return string
     */
    public function getVerbName(): string
    {
        return ICRUDVerb::CREATE;
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

        $createRules = $crudProvider->getCreateRules($request);

        if ($crudProvider->shouldFilterRequestParamsByRules()) {
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

        $object = $crudStorage->store($crudProvider, $input_data);
        $with = $crudProvider->getDefaultShowRelations();
        if (!is_null($with)) {
            $object->load($with);
        }

        CRUDCreated::dispatch(Auth::user(), $object, get_class($crudProvider), Carbon::now());

        return $object;
    }
}
