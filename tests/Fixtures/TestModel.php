<?php

namespace DBonner\Presenter\Tests\Fixtures;

use DBonner\Presenter\Presentable;
use Illuminate\Database\Eloquent\Model;

class TestModel extends Model implements Presentable
{
    protected $attributes = [
        'foo' => 'bar',
    ];

    public function relatedModel()
    {
        return $this->hasOne(TestRelatedModel::class);
    }
}
