<?php

namespace DBonner\Presenter\Facades;

use Illuminate\Support\Facades\Facade;

class Presenter extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'dbonner.presenter';
    }
}
