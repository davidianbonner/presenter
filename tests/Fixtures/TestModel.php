<?php

namespace DavidIanBonner\Presenter\Tests\Fixtures;

use DavidIanBonner\Presenter\Presentable;
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
