<?php

namespace DBonner\Presenter\Tests;

use Illuminate\Support\Collection;
use Orchestra\Testbench\TestCase as Orchestra;
use DBonner\Presenter\PresenterServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageAliases($app)
    {
        return [
            'Presenter' => \DBonner\Presenter\Facades\Presenter::class
        ];
    }

    protected function getPackageProviders($app)
    {
        return [
            PresenterServiceProvider::class
        ];
    }
}
