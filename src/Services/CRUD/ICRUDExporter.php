<?php

namespace Larapress\CRUD\Services\CRUD;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

interface ICRUDExporter
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Larapress\CRUD\Services\CRUD\ICRUDProvider $provider
     * @return mixed
     */
    public function getResponseForQueryExport(Request $request, Builder $query, ICRUDProvider $provider);
}
