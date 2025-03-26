<?php

namespace CheshireCatSdk\Facades;

use Illuminate\Support\Facades\Facade;

class CheshireCatFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'cheshirecat';
    }
    public static function wsClient()
    {
        return static::$app->make('cheshirecat')->wsClient;
    }
}