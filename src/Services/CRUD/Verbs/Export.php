<?php

namespace Larapress\CRUD\Services\CRUD\Verbs;

use Illuminate\Http\Request;
use Larapress\CRUD\Exceptions\AppException;
use Larapress\CRUD\Services\CRUD\ICRUDService;
use Larapress\CRUD\Services\CRUD\ICRUDVerb;

class Export implements ICRUDVerb {
    /**
     * Undocumented function
     *
     * @return string
     */
    public function getVerbName(): string
    {
        return ICRUDVerb::EXPORT;
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
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '1G');
    }
}
