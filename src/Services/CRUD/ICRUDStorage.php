<?php

namespace Larapress\CRUD\Services\CRUD;

use Illuminate\Database\Eloquent\Model;
use Larapress\CRUD\Services\CRUD\ICRUDProvider;

interface ICRUDStorage
{

    /**
     * Create new resource in storage.
     *
     * @param ICRUDProvider $crudProvider
     * @param array $args
     * @return null|Model
     */
    public function store(ICRUDProvider $crudProvider, array $args);

    /**
     * Update a specified resource in storage.
     *
     * @param ICRUDProvider $crudProvider
     * @param int     $object
     * @param array   $args
     * @return null|Model
     */
    public function update(ICRUDProvider $crudProvider, $object, $args);
}
