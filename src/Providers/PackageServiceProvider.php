<?php

namespace Larapress\CRUD\Providers;

use Illuminate\Support\ServiceProvider;
use Larapress\CRUD\Base\BaseCRUDService;
use Larapress\CRUD\Base\ICRUDService;
use Larapress\CRUD\Commands\CRUDPermissionsCommands;
use Larapress\CRUD\Repository\IRoleRepository;
use Larapress\CRUD\Repository\RoleRepository;
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
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
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
