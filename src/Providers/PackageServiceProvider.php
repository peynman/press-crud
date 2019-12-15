<?php

namespace Larapress\CRUD\Providers;

use Illuminate\Support\ServiceProvider;
use Larapress\CRUD\Base\BaseCRUDService;
use Larapress\CRUD\Base\ICRUDService;

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
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

        $this->publishes(
            [
            __DIR__.'/../../config/crud.php' => config_path('larapress/crud.php'),
            ],
            ['config', 'larapress', 'larapress-crud']
        );
    }
}
