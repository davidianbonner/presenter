<?php

namespace DavidIanBonner\Presenter\Tests\Fixtures;

use Illuminate\Support\Arr;
use DavidIanBonner\Presenter\Presentable;
use Illuminate\Contracts\Support\Arrayable;

class TestPresentable implements Presentable, Arrayable
{
    public $items = [
        'foo' => 'bar',
        'bar' => 'foo'
    ];

    public function clearArray()
    {
        $this->items = [];
    }

    public function __get($key)
    {
        return Arr::get($this->items, $key, null);
    }

    public function __isset($key)
    {
        return !is_null($this->{$key});
    }

    public function toArray()
    {
        return $this->items;
    }
}
