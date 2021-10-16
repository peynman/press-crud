<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Larapress\CRUD\Services\CRUD\CRUDController;
use Larapress\CRUD\Services\CRUD\ICRUDBroadcast;
use Larapress\CRUD\Services\RepoSources\RepositoryController;
use Larapress\CRUD\Services\CRUD\ICRUDProvider;

if (!function_exists('registerProviders')) {
    function registerProviders($providers)
    {
        if (is_null($providers) || !is_array($providers)) {
            return;
        }

        foreach ($providers as $providerClass) {
            if (Str::startsWith($providerClass, 'include::')) {
                $include = Str::substr($providerClass, Str::length('include::'));
                registerProviders(config($include));
            } else {
                /** @var ICRUDProvider */
                $provider = new $providerClass();
                call_user_func(
                    [
                        CRUDController::class, 'registerCrudRoutes'
                    ],
                    $provider
                );
            }
        }
    }
}

Route::middleware(config('larapress.crud.middlewares'))
    ->prefix(config('larapress.crud.prefix'))
    ->group(function () {
        registerProviders(config('larapress.crud.permissions'));
    });

Route::middleware(config('larapress.crud.public-middlewares'))
    ->prefix(config('larapress.crud.prefix'))
    ->group(function () {
        RepositoryController::registerPublicApiRoutes();
    });

Route::middleware(config('larapress.crud.broadcast-middlewares'))
    ->prefix(config('larapress.crud.prefix'))
    ->post('/broadcast/auth', function (Request $request) {
        /** @var ICRUDBroadcast */
        $service = app(ICRUDBroadcast::class);
        return $service->authenticateRequest($request);
    });
