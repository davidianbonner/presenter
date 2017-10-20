<?php

namespace DBonner\Presenter\Tests;

use Mockery;
use ArrayAccess;
use DBonner\Presenter\Presenter;
use Illuminate\Support\Collection;
use DBonner\Presenter\Presentable;
use DBonner\Presenter\Transformer;
use Illuminate\Contracts\Container\Container;
use Illuminate\Pagination\LengthAwarePaginator;
use DBonner\Presenter\Tests\Fixtures\TestModel;
use DBonner\Presenter\Tests\Fixtures\TestPresentable;
use DBonner\Presenter\Tests\Fixtures\TestTransformer;
use DBonner\Presenter\Tests\Fixtures\TestRelatedModel;
use DBonner\Presenter\Facades\Presenter as PresenterFacade;
use DBonner\Presenter\Tests\Fixtures\TestRelatedTransformer;

class PresenterTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->presenter = app(Presenter::class);

        $this->transformers = [
            TestPresentable::class => TestTransformer::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('presenter.transformers', [
            'foo' => 'bar'
        ]);
    }

    /** @test */
    function it_can_find_config()
    {
        $this->assertEquals('bar', config('presenter.transformers.foo'));
    }

    /** @test */
    function transformers_can_be_added_to_the_presenter()
    {
        $m = Mockery::mock(Presenter::class, [app()->make(Container::class)])->makePartial();
        $m->pushTransformers($this->transformers);
        $m->pushTransformers(['foo' => 'bar']);

        $this->assertSame(
            array_merge($this->transformers, ['foo' => 'bar']),
            $m->allTransformers()
        );
    }

    /** @test */
    function it_can_transform_a_single_presentable_object()
    {
        PresenterFacade::pushTransformers($this->transformers);
        $transformer = PresenterFacade::transform(new TestPresentable);

        $this->assertInstanceOf(Transformer::class, $transformer);
    }

    /** @test */
    function it_can_transform_an_array_of_presentable_objects()
    {
        PresenterFacade::pushTransformers($this->transformers);

        $transformer = PresenterFacade::transform([
            new TestPresentable,
            new TestPresentable,
            new TestPresentable,
        ]);

        $this->assertCount(3, $transformer);

        foreach ($transformer as $value) {
            $this->assertInstanceOf(Transformer::class, $value);
        }
    }

    /** @test */
    function it_can_transform_a_collection_of_presentable_objects()
    {
        PresenterFacade::pushTransformers($this->transformers);

        $collection = PresenterFacade::transform(collect([
            new TestPresentable,
            new TestPresentable,
            new TestPresentable,
            ['not_a_presentable_object']
        ]));

        $this->assertCount(4, $collection);
        $this->assertInstanceOf(Collection::class, $collection);
        $collection->filter(function ($entity) {
            return $entity instanceof Transformer;
        })->tap(function ($collection) {
            $this->assertCount(3, $collection);
        });
    }

    /** @test */
    function it_can_transform_a_collection_of_paginated_objects()
    {
        PresenterFacade::pushTransformers($this->transformers);

        $paginator = new LengthAwarePaginator([
            new TestPresentable,
            new TestPresentable,
            new TestPresentable,
        ], 3, 10, 1);

        $paginator = PresenterFacade::transform($paginator);

        $this->assertCount(3, $paginator);
        $this->assertInstanceOf(LengthAwarePaginator::class, $paginator);
        foreach ($paginator as $value) {
            $this->assertInstanceOf(Transformer::class, $value);
        }
    }

    /** @test */
    function it_can_transform_loaded_relationships_on_eloquent_models()
    {
        PresenterFacade::pushTransformers([
            TestModel::class => TestTransformer::class,
            TestRelatedModel::class => TestRelatedTransformer::class,
        ]);

        $model = new TestModel;
        $model->setRelation('relatedModel', new TestRelatedModel);
        $model->setRelation('relatedNonPresentableModel', 'not_even_an_object');

        $transformer = PresenterFacade::transform($model);

        $this->assertInstanceOf(TestTransformer::class, $transformer);
        $this->assertInstanceOf(TestRelatedTransformer::class, $transformer->relatedModel);
        $this->assertEquals('not_even_an_object', $model->relatedNonPresentableModel);
    }
}
