<?php

namespace Larapress\CRUD\Base;

use Illuminate\Database\Query\Builder;

interface ICRUDExporter
{
    public function getResponseForQueryExport(Builder $query, ICRUDProvider $provider);
}
