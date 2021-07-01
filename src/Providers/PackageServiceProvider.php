<?php

namespace Larapress\CRUD\Providers;

use Illuminate\Support\ServiceProvider;
use Larapress\CRUD\Commands\CreateSuperUser;
use Larapress\CRUD\Services\RBAC\IPermissionsService;
use Larapress\CRUD\Services\CRUD\CRUDService;
use Larapress\CRUD\Services\CRUD\ICRUDService;
use Larapress\CRUD\Commands\UpdatePermissions;
use Larapress\CRUD\Repository\IPermissionsRepository;
use Larapress\CRUD\Repository\IRoleRepository;
use Larapress\CRUD\Repository\PermissionsRepository;
use Larapress\CRUD\Repository\RoleRepository;
use Larapress\CRUD\Services\CRUD\BaseCRUDBroadcast;
use Larapress\CRUD\Services\CRUD\ICRUDBroadcast;
use Larapress\CRUD\Services\RBAC\PermissionsService;
use Larapress\CRUD\Validations\DateTimeZonedValidator;
use Larapress\CRUD\Validations\DBObjectIDsValidator;
use Larapress\CRUD\Validations\NumericFarsiValidator;

class PackageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(ICRUDService::class, CRUDService::class);
        $this->app->bind(IRoleRepository::class, RoleRepository::class);
        $this->app->bind(IPermissionsRepository::class, PermissionsRepository::class);
        $this->app->bind(IPermissionsService::class, PermissionsService::class);
        $this->app->bind(ICRUDBroadcast::class, CRUDBroadcast::class);
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
        $this->loadRoutesFrom(__DIR__.'/../../routes/channels.php');
        $this->loadMigrationsFrom(__DIR__.'/../../migrations');

        $this->publishes(
            [
            __DIR__.'/../../config/crud.php' => config_path('larapress/crud.php'),
            ],
            ['config', 'larapress', 'larapress-crud']
        );

        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateSuperUser::class,
                UpdatePermissions::class,
            ]);
        }


        NumericFarsiValidator::register();
        DateTimeZonedValidator::register();
    }
}
