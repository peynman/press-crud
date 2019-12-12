<?php

namespace Larapress\CRUD\Base;

use Illuminate\Http\Request;

interface ICRUDStorage
{
    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param array   $args
     *
     * @return object
     */
    public function store(Request $request, $args);

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int     $object
     * @param array   $args
     */
    public function update(Request $request, $object, $args);
}
