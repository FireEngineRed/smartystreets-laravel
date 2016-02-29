# smartystreets-laravel
Laravel (PHP) SDK for using SmartyStreets geocoding.

Only attempting Address Verify at this time; willing to accept pull requests that fill in the other functionalities too (Zipcode Verify, Autocomplete, and Address Extraction).

Example Usage
--------------

```php
$response = SmartyStreets::addressQuickVerify(array(
    'street'=>'P.O. Box 1017',
    'city'=>'Havertown',
    'state'=>'PA',
));
```
Methods are available (addressAddToRequest && addressGetCandidates) to check multiple addresses with one POST, but addressQuickVerify only handles one address at a time.

Further API details, including request and response fields, available at SmartyStreets: https://smartystreets.com/docs/address


How to Install
---------------

### Laravel 5.0 +

1.  Install the `fireenginered/smartystreets-laravel` package

    ```shell
    $ composer require fireenginered/smartystreets-laravel:dev-master
    ```

1. Update `config/app.php` to activate SmartyStreets

    ```php
    # Add `SmartyStreetsLaravelServiceProvider` to the `providers` array
    'providers' => array(
        ...
        'FireEngineRed\SmartyStreetsLaravel\SmartyStreetsServiceProvider',
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
