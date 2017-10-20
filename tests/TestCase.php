<?php

namespace DavidIanBonner\Presenter\Tests;

use Illuminate\Support\Collection;
use Orchestra\Testbench\TestCase as Orchestra;
use DavidIanBonner\Presenter\PresenterServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageAliases($app)
    {
        return [
            'Presenter' => \DavidIanBonner\Presenter\Facades\Presenter::class
        ];
    }

    protected function getPackageProviders($app)
    {
        return [
            PresenterServiceProvider::class
        ];
    }
}
