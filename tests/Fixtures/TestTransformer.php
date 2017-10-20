<?php

namespace DBonner\Presenter\Tests\Fixtures;

use DBonner\Presenter\Transformer;

class TestTransformer extends Transformer
{
    public function foo()
    {
        return $this->object->foo.'_mutated';
    }
}
