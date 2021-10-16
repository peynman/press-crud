<?php

namespace Larapress\CRUD\Services\CRUD;

use Illuminate\Http\Request;

interface ICRUDVerb {
    const VIEW = 'query';
    const EDIT = 'update';
    const DELETE = 'destroy';
    const CREATE = 'store';
    const EXPORT = 'export';
    const SHOW = 'show';

    /**
     * Undocumented function
     *
     * @return string
     */
    public function getVerbName(): string;

    /**
     * Undocumented function
     *
     * @param ICRUDService $service
     * @param Request $request
     * @param [type] ...$args
     *
     * @return void
     */
    public function handle(ICRUDService $service, Request $request, ...$args);
}
