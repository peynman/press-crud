<?php

namespace Larapress\CRUD\Services\CRUD\Traits;

use Illuminate\Database\Eloquent\Model;

trait CRUDRelationSyncTrait
{
    /**
     * @param string $relation
     * @param Model $object
     * @param array $data
     * @param string $class
     */
    protected function saveHasManyRelation($relation, $object, $data, $class)
    {
        $models = [];
        foreach ($data[$relation] as $datum) {
            $models[] = new $class($datum);
        }
        /** @var \Illuminate\Database\Eloquent\Relations\HasMany $builder */
        $builder = call_user_func([$object, $relation]);
        $builder->saveMany($models);
    }

    /**
     * @param string $relation
     * @param Model $object
     * @param array $data
     */
    protected function syncWithoutDetachingBelongsToManyRelation($relation, $object, $data)
    {
        if (!empty($data[$relation])) {
            $ids = [];
            foreach ($data[$relation] as $datum) {
                $ids[] = $datum['id'];
            }

            /** @var \Illuminate\Database\Eloquent\Relations\BelongsToMany $builder */
            $builder = call_user_func([$object, $relation]);
            $builder->syncWithoutDetaching($ids);
        }
    }

    /**
     * @param string $relation
     * @param Model $object
     * @param array $data
     */
    protected function syncBelongsToManyRelation(
        $relation,
        $object,
        $data,
        $canBeAttachedCallback = null,
        $generateAttributeCallback = null,
        $getIdCallback = null
    ) {
        if (!empty($data[$relation])) {
            $ids = [];
            foreach ($data[$relation] as $datum) {
                if (!is_null($canBeAttachedCallback)) {
                    if (!$canBeAttachedCallback($datum)) {
                        continue;
                    }
                }

                $id = is_null($getIdCallback) ? $datum : $getIdCallback($datum);
                if (is_null($generateAttributeCallback)) {
                    $ids[] = $id;
                } else {
                    $ids[$id] = $generateAttributeCallback($datum);
                }
            }

            /** @var \Illuminate\Database\Eloquent\Relations\BelongsToMany $builder */
            $builder = call_user_func([$object, $relation]);
            $builder->sync($ids);
        }
    }
}
