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
            $this->syncRelations('store', $crudProvider->getAutoSyncRelations(), $object, $data);

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

        $data = $crudProvider->onBeforeObjectUpdate($object, $data);

        return DB::transaction(
            function () use ($data, $object, $crudProvider) {
                if (!$crudProvider->onBeforeAccess($object)) {
                    throw new AppException(AppException::ERR_OBJ_ACCESS_DENIED);
                }

                $object->update($data);
                $this->syncRelations('update', $crudProvider->getAutoSyncRelations(), $object, $data);

                $crudProvider->onAfterUpdate($object, $data);
                return $object;
            }
        );

    }

    /**
     * Undocumented function
     *
     * @param [type] $relation
     * @param [type] $callback
     * @param [type] $object
     * @param [type] $data
     * @return void
     */
    protected function syncRelation($relation, $callback, $object, $data)
    {
        $saveAfter = false;
        /** @var HasMany|BelongsToMany|BelongsTo $builder */
        $builder = call_user_func([$object, $relation]);
        $builderClass = class_basename($builder);

        switch ($builderClass) {
            case class_basename(HasMany::class):
                $builder->saveMany($callback($object, $data));
                break;
            case class_basename(BelongsTo::class):
                $builder->associate($callback($object, $data));
                $saveAfter = true;
                break;
            case class_basename(BelongsToMany::class):
                $rel = $callback($object, $data);
                if (is_array($rel)) {
                    $builder->attach($rel[0], isset($rel[1]) ? $rel[1] : []);
                } else {
                    $builder->attach($rel);
                }
                break;
        }

        return $saveAfter;
    }

    /**
     * @param $method
     * @param array $autoSyncRelations
     * @param \Illuminate\Database\Eloquent\Model $object
     * @param array $data
     */
    protected function syncRelations($method, $autoSyncRelations, $object, $data)
    {
        $saveAfter = false;
        foreach ($autoSyncRelations as $relation => $callback) {
            if (is_callable($callback)) {
                $saveAfter = $saveAfter || $this->syncRelation($relation, $callback, $object, $data);
            } elseif (isset($callback[$method]) && is_callable($callback[$method])) {
                $saveAfter = $saveAfter || $this->syncRelation($relation, $callback[$method], $object, $data);
            }
        }

        if ($saveAfter) {
            $object->save();
        }
    }
}
