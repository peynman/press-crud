<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::middleware(config('larapress.crud.middleware'))
    ->prefix(config('larapress.crud.prefix'))
    ->group(function () {
        $controllers = config('larapress.crud.controllers');
        $registerControllers = function($controllers, $registerFunction) {
            if (is_null($controllers)) { return; }

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