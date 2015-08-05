<?php

namespace FireEngineRed\SmartyStreetsLaravel;

use Illuminate\Support\ServiceProvider;

class SmartyStreetsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $source = realpath(__DIR__.'/config.php');
		$this->publishes([$source => config_path('smartystreets.php')]);
		$this->mergeConfigFrom($source, 'smartystreets');
    }

    public function register()
    {
        $this->app->bind('smartystreets', function ($app) {
            return new SmartyStreetsService();
        });
    }

    public function provides()
    {
        return array("smartystreets");
    }
}
