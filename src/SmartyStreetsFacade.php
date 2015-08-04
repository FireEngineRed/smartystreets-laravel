<?php 

namespace FireEngineRed\SmartyStreetsLaravel;

use Illuminate\Support\Facades\Facade;

class SmartyStreetsFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'smartystreets';
    }
}
