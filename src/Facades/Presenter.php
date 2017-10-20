<?php

namespace DavidIanBonner\Presenter\Facades;

use Illuminate\Support\Facades\Facade;

class Presenter extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'davidianbonner.presenter';
    }
}
