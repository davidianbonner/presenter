<?php

namespace DBonner\Presenter\Tests;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use DBonner\Presenter\Tests\Fixtures\TestModel;
use DBonner\Presenter\Tests\Fixtures\TestTransformer;

class ResponseMacroTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('view.paths', [__DIR__.'/views']);
        $app['config']->set('presenter.transformers', [
            TestModel::class => TestTransformer::class,
        ]);
    }

    /** @test */
    function it_will_transform_any_presentables_and_return_the_view()
    {
        $response = Response::present('test', ['model' => new TestModel], 201, ['X-FOO' => 'bar']);

        $this->assertInstanceOf(TestTransformer::class, $response->original->getData()['model']);
        $this->assertEquals('bar_mutated', $response->original->getData()['model']->foo);
        $this->assertEquals('test', $response->original->getName());
        $this->assertTrue($response->headers->has('X-FOO'));
        $this->assertEquals(201, $response->getStatusCode());
    }

    /** @test */
    function it_will_transform_any_presentables_and_return_the_json()
    {
        $response = JsonResponse::present(['model' => new TestModel], 201, ['X-FOO' => 'bar']);

        $this->assertInstanceOf(TestTransformer::class, $response->original['model']);
        $this->assertEquals('bar_mutated', $response->original['model']->foo);
        $this->assertTrue($response->headers->has('X-FOO'));
        $this->assertEquals(201, $response->getStatusCode());

        $json = json_decode((string) $response->getContent());

        $this->assertEquals('bar_mutated', $json->model->foo);
    }
}
