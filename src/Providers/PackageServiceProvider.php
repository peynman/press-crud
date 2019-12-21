<?php

namespace Larapress\CRUD\Providers;

use Illuminate\Support\ServiceProvider;
use Larapress\CRUD\Base\BaseCRUDService;
use Larapress\CRUD\Base\ICRUDService;
use Larapress\CRUD\Commands\AccountManager;
use Larapress\CRUD\CRUD\RoleCRUDProvider;

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

        $this->app->singleton(RoleCRUDProvider::class, function ($app) {
            return new RoleCRUDProvider();
        });
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
                AccountManager::class,
            ]);
        }
    }
}
