<?php

namespace DavidIanBonner\Presenter\Tests;

use Mockery;
use Exception;
use ArrayAccess;
use PHPUnit\Framework\Assert;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use DavidIanBonner\Presenter\Tests\Fixtures\TestPresentable;
use DavidIanBonner\Presenter\Tests\Fixtures\TestTransformer;

class TransformerTest extends TestCase
{
    protected $presentable;
    protected $transformer;

    protected function setUp()
    {
        parent::setUp();

        $this->presentable = new TestPresentable;
        $this->transformer = TestTransformer::make($this->presentable);
    }

    /** @test */
    function it_boots_the_transformer_when_setting_the_presentable()
    {
        $m = Mockery::mock(TestTransformer::class)
                    ->makePartial()
                    ->shouldAllowMockingProtectedMethods();

        $m->shouldReceive('bootTransformer')
            ->once()
            ->with($presentable = new TestPresentable);

        $m->setPresentableObject($presentable);
    }

    /** @test */
    function it_can_be_accessed_as_an_array()
    {
        $this->assertTrue($this->transformer instanceof ArrayAccess);
        $this->assertEquals('bar_mutated', $this->transformer->offsetGet('foo'));
        $this->assertTrue($this->transformer->offsetExists('foo'));

        $catch = 0;

        try {
            $this->transformer->offsetUnset('foo');
        } catch (Exception $e) { $catch++; }

        try {
            $this->transformer->offsetSet('foo_bar', null);
        } catch (Exception $e) { $catch++; }

        if ($catch !== 2) {
            Assert::fail('[offsetUnset|offsetSet] should throw an expection.');
        }
    }

    /** @test */
    function it_can_be_cast_to_an_array_with_magic_methods_called()
    {
        $this->assertTrue(
            $this->transformer instanceof Arrayable
        );

        $this->assertEquals(
            ['foo' => 'bar_mutated', 'bar' => 'foo'],
            $this->transformer->toArray()
        );
    }

    /** @test */
    function it_can_be_cast_to_a_json_representation()
    {
        $this->assertTrue(
            $this->transformer instanceof Jsonable
        );

        $this->assertTrue(
            is_string($this->transformer->toJson())
        );

        $this->assertEquals(
            json_encode(['foo' => 'bar_mutated', 'bar' => 'foo']),
            $this->transformer->toJson()
        );
    }

    /** @test */
    function it_will_get_a_property_via_method_mutation()
    {
        $mock = Mockery::mock(TestTransformer::class)->makePartial();

        $mock->shouldNotReceive('bar');
        $mock->shouldReceive('foo')->once()->andReturn('bar_mutated');

        $mock->foo;
        $mock->bar;
    }

    /** @test */
    function confirm_is_set()
    {
        $this->assertTrue(isset($this->transformer->foo));
        $this->assertFalse(isset($this->transformer->not_set));
    }
}
