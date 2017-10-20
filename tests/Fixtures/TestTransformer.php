<?php

namespace DavidIanBonner\Presenter\Tests\Fixtures;

use DavidIanBonner\Presenter\Transformer;

class TestTransformer extends Transformer
{
    public function foo()
    {
        return $this->object->foo.'_mutated';
    }
}
