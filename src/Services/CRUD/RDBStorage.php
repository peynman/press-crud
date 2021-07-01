<?php

namespace Larapress\CRUD\Services\CRUD;

use Illuminate\Support\Facades\DB;
use Larapress\CRUD\Services\CRUD\ICRUDProvider;
use Illuminate\Database\Eloquent\Model;
use Larapress\CRUD\Exceptions\AppException;

class RDBStorage implements ICRUDStorage
{

    /**
     * Undocumented function
     *
     * @param ICRUDProvider $crudProvider
     * @param array $args
     *
     * @return null|Model
     */
    public function store(ICRUDProvider $crudProvider, array $args)
    {
        $object = null;
        $data = $crudProvider->onBeforeCreate($args);
        try {
            DB::beginTransaction();
            $object = call_user_func([$crudProvider->getModelClass(), 'create'], $data);
            $crudProvider->onAfterCreate($object, $data);
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return $object;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ICRUDProvider $crudProvider
     * @param int     $object
     * @param array   $args
     *
     * @return null|Model
     */
    public function update(ICRUDProvider $crudProvider, $objectId, $args)
    {
        $data = $crudProvider->onBeforeUpdate($args);

        $object = $crudProvider->getObjectFromID($objectId);
        if (is_null($object)) {
            throw new AppException(AppException::ERR_OBJECT_NOT_FOUND);
        }

        if (!$crudProvider->onBeforeAccess($object)) {
            throw new AppException(AppException::ERR_OBJ_ACCESS_DENIED);
        }

        $data = $crudProvider->onBeforeObjectUpdate($object, $data);

        return DB::transaction(
            function () use ($data, $object, $crudProvider) {
                $object->update($data);
                $crudProvider->onAfterUpdate($object, $data);
                return $object;
            }
        );

    }
}
