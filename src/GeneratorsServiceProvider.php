<?php

namespace TodReacher\Generators;

use Illuminate\Support\ServiceProvider;

class GeneratorsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerModelTraitGenerator();
        $this->registerModelServiceGenerator();
        $this->registerModelRequestGenerator();
        $this->registerModelResourceGenerator();
        $this->registerModelControllerGenerator();
    }

    private function registerModelTraitGenerator()
    {
        $this->app->singleton('command.todreacher.model.trait', function ($app) {
            return $app['TodReacher\Generators\Commands\ModelTraitCommand'];
        });

        $this->commands('command.todreacher.model.trait');
    }

    private function registerModelServiceGenerator()
    {
        $this->app->singleton('command.todreacher.model.service', function ($app) {
            return $app['TodReacher\Generators\Commands\ModelServiceCommand'];
        });

        $this->commands('command.todreacher.model.service');
    }

    private function registerModelRequestGenerator()
    {
        $this->app->singleton('command.todreacher.model.request', function ($app) {
            return $app['TodReacher\Generators\Commands\ModelRequestCommand'];
        });

        $this->commands('command.todreacher.model.request');
    }

    private function registerModelResourceGenerator()
    {
        $this->app->singleton('command.todreacher.model.resource', function ($app) {
            return $app['TodReacher\Generators\Commands\ModelResourceCommand'];
        });

        $this->commands('command.todreacher.model.resource');
    }

    private function registerModelControllerGenerator()
    {
        $this->app->singleton('command.todreacher.model.controller', function ($app) {
            return $app['TodReacher\Generators\Commands\ModelControllerCommand'];
        });

        $this->commands('command.todreacher.model.controller');
    }
}
