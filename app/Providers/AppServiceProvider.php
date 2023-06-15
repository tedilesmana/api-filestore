<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(\L5Swagger\L5SwaggerServiceProvider::class);
        $models = array(
            'Auth',
            'GatewayApplication',
            'GatewayModule',
            'GatewayFeature',
            'GatewayManager',
            'SeederDb',
        );

        foreach ($models as $model) {
            $this->app->bind("App\Repositories\Interfaces\\{$model}\\{$model}RepositoryInterface", "App\Repositories\Eloquent\\{$model}\\{$model}Repository");
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
