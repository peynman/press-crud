<?php

namespace Larapress\CRUD\Providers;

use Illuminate\Support\ServiceProvider;
use Larapress\CRUD\Services\IPermissionsService;
use Larapress\CRUD\Services\BaseCRUDService;
use Larapress\CRUD\Services\ICRUDService;
use Larapress\CRUD\Commands\CRUDPermissionsCommands;
use Larapress\CRUD\Repository\IPermissionsRepository;
use Larapress\CRUD\Repository\IRoleRepository;
use Larapress\CRUD\Repository\PermissionsRepository;
use Larapress\CRUD\Repository\RoleRepository;
use Larapress\CRUD\Services\BaseCRUDBroadcast;
use Larapress\CRUD\Services\IBaseCRUDBroadcast;
use Larapress\CRUD\Services\PermissionsService;
use Larapress\CRUD\Validations\DateTimeZonedValidator;
use Larapress\CRUD\Validations\DBObjectIDsValidator;

class PackageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(ICRUDService::class, BaseCRUDService::class);
        $this->app->bind(IRoleRepository::class, RoleRepository::class);
        $this->app->bind(IPermissionsRepository::class, PermissionsRepository::class);
        $this->app->bind(IPermissionsService::class, PermissionsService::class);
        $this->app->bind(IBaseCRUDBroadcast::class, BaseCRUDBroadcast::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'larapress');
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
        $this->loadMigrationsFrom(__DIR__.'/../../migrations');

        $this->publishes(
            [
            __DIR__.'/../../config/crud.php' => config_path('larapress/crud.php'),
            ],
            ['config', 'larapress', 'larapress-crud']
        );

        if ($this->app->runningInConsole()) {
            $this->commands([
                CRUDPermissionsCommands::class,
            ]);
        }

        DBObjectIDsValidator::register();
        DateTimeZonedValidator::register();
    }
}
