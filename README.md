# smartystreets-laravel
Laravel (PHP) SDK for using SmartyStreets geocoding.

Only attempting Address Validation at this time; willing to accept pull requests that fill in the Zipcode Validation functionality too.


How to Install
---------------

### Laravel 5.0 +

1.  Install the `fireenginered/smartystreets-laravel` package

    ```shell
    $ composer require smartystreets/smartystreets-laravel:dev-master
    ```

1. Update `config/app.php` to activate SmartyStreets

    ```php
    # Add `SmartyStreetsLaravelServiceProvider` to the `providers` array
    'providers' => array(
        ...
        'FireEngineRed\SmartyStreetsLaravel\SmartyStreetsLaravelServiceProvider',
    )

    # Add the `SmartyStreetsFacade` to the `aliases` array
    'aliases' => array(
        ...
        'SmartyStreets'  => 'FireEngineRed\SmartyStreetsLaravel\SmartyStreetsFacade',
    )
    ```

1. Create the configuration file `config/smartystreets.php`:

    ```shell
    $ php artisan vendor:publish
    ```

1. Configure your API credentials in the config file.

    ```shell
	'authId' 	=> 'raw ID here',
	'authToken'	=> 'raw token here',
    ```
    
Alternately, replace the values there with env() calls, and put the credentials in your .env file