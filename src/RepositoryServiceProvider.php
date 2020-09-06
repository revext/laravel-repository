<?php

namespace Revext\Repository;

use Illuminate\Support\ServiceProvider;
use Revext\Repository\Commands\MakeRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/repository.php' => config_path('repository.php'),
            ], 'config');
            $this->commands([
                MakeRepository::class
            ]);
        }

        $this->mergeConfigFrom(__DIR__.'/../config/repository.php', 'repository');

    }

    public function register()
    {
        // $this->app->bind(QueryBuilderRequest::class, function ($app) {
        //     return QueryBuilderRequest::fromRequest($app['request']);
        // });
    }
}
