<?php 

namespace FireEngineRed\SmartyStreetsLaravel;

use Illuminate\Support\ServiceProvider;

class SmartyStreetsLaravelServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes(array(
            __DIR__.'/config.php' => config_path('smartystreets.php'),
        ));
    }

    public function register()
    {
        $config = isset($app['config']['services']['smartystreets']) ? $app['config']['services']['smartystreets'] : null;
        if (is_null($config)) {
            $config = $app['config']['smartystreets'] ?: $app['config']['smartystreets::config'];
        }
        
        $this->app->bind('smartystreets', function ($app) use ($config) {
            return new SmartyStreets($config['authId'], $config['authToken']);
        });
    }

    public function provides()
    {
        return array("smartystreets");
    }
}
