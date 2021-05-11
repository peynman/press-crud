<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Larapress\CRUD\Services\CRUD\ICRUDBroadcast;

Route::middleware(config('larapress.crud.middlewares'))
    ->prefix(config('larapress.crud.prefix'))
    ->group(function () {
        $controllers = config('larapress.crud.controllers');
        $registerControllers = function ($controllers, $registerFunction) {
            if (is_null($controllers)) {
                return;
            }

            foreach ($controllers as $controller) {
                if (Str::startsWith($controller, 'include::')) {
                    $include = Str::substr($controller, Str::length('include::'));
                    $registerFunction(config($include), $registerFunction);
                } else {
                    call_user_func([$controller, 'registerRoutes']);
                }
            }
        };
        $registerControllers($controllers, $registerControllers);
    });

Route::middleware(config('larapress.crud.broadcast-middlewares'))
    ->prefix(config('larapress.crud.prefix'))
    ->post('/broadcast/auth', function (Request $request) {
        /** @var ICRUDBroadcast */
        $service = app(ICRUDBroadcast::class);
        return $service->authenticateRequest($request);
    });
