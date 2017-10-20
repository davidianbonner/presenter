<?php

namespace Tests\Unit;

use Mockery as m;
use Tests\TestCase;
use Illuminate\Support\Collection;
use DBonner\Depot\Repositories\RepositoryCriteria;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class RepositoryCriteriaTest extends TestCase
{
    /** @test */
    function repository_criteria_instance_of_collection()
    {
        $this->assertTrue(new RepositoryCriteria instanceof Collection);
    }

    /** @test */
    function criteria_is_applied()
    {
        // Create a quick test moel
        $builder = (new class extends \Illuminate\Database\Eloquent\Model {
            protected $fillable = ['name'];
        })->query();

        // Create a new RepositoryCriteria object and pass in a mocked criteria
        // via the constructor
        $collection = new RepositoryCriteria([m::mock('App\Utilities\Repositories\Criterion', function($mock) use ($builder) {
            $mock->shouldReceive('apply')->with($builder)->andReturnUsing(function ($builder) {
                $builder->where('name', 'TESTCASE');
            });
        })]);

        // Use the collections `push` method to test that criteria can
        // be added via method calls
        $collection->push(m::mock('App\Utilities\Repositories\Criterion', function($mock) use ($builder) {
            $mock->shouldReceive('apply')->with($builder)->andReturnUsing(function ($builder) {
                $builder->orderBy('name', 'asc');
            });
        }));

        $query = $collection->apply($builder)->getQuery();
        // Assert that the criteria has been applied to the query builder objects
        $this->assertArraySubset([0 => ['column' => 'name', 'value' => 'TESTCASE']], $query->wheres);
        $this->assertArraySubset([0 => ['column' => 'name', 'direction' => 'asc']], $query->orders);
    }

    /** @test */
    function criteria_can_be_added_and_removed()
    {
        $criteria = m::mock('WhereCriterion');
        $collection = new RepositoryCriteria([$criteria]);

        $collection->push($criteria);
        $collection->push($criteria);
        $collection->push($criteria);
        $collection->pop();

        $this->assertEquals($collection->count(), 3);

        $collection->push($criteria);
        $collection->pop();
        $collection->push($criteria);

        $this->assertEquals($collection->count(), 4);
    }

    /** @test */
    function repository_criteria_apply_returns_query_builder()
    {
        $collection = new RepositoryCriteria;
        $model = \App\Users\User::where('id', '1');

        $applied = $collection->apply($model, $collection);

        $this->assertEquals(get_class($applied), \Illuminate\Database\Eloquent\Builder::class);
    }
}
