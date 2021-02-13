<?php

namespace Larapress\CRUD\Translation;

use Illuminate\Translation\TranslationServiceProvider;

class PackageTranslationServiceProvider extends TranslationServiceProvider
{
    /**
     * Register the translation line loader.
     *
     * @return void
     */
    protected function registerLoader()
    {
        $this->app->singleton('translation.loader', function ($app) {
            return new PackageFileLoader($app['files'], $app['path.lang']);
        });
    }
}
