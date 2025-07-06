<?php

namespace Tests\Unit\Models;

use App\Http\Filters\QueryFilter;
use App\Models\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_includable_property_is_array(): void
    {
        $this->assertIsArray(Model::$includable);
        $this->assertEquals([], Model::$includable);
    }

    public function test_filter_scope_applies_query_filter(): void
    {
        // Create a test model that extends the base Model
        $testModel = new TestModelForFilterScope();
        
        // Create a mock QueryFilter
        $mockFilter = $this->createMock(TestQueryFilter::class);
        
        // Create a query builder
        $query = $testModel->newQuery();
        
        // Set up the expectation that apply() will be called
        $mockFilter->expects($this->once())
                   ->method('apply')
                   ->with($query)
                   ->willReturn($query);
        
        // Test the filter scope
        $result = $testModel->filter($mockFilter);
        
        $this->assertInstanceOf(Builder::class, $result);
    }

    public function test_visible_to_scope_returns_all_when_user_has_view_any_permission(): void
    {
        $user = User::factory()->create();
        $testModel = new TestModelForVisibleToScope();
        
        // Mock Gate to allow viewAny
        Gate::shouldReceive('allows')
            ->once()
            ->with('viewAny', $testModel)
            ->andReturn(true);
        
        $query = $testModel->newQuery();
        $result = $testModel->visibleTo($user);
        
        $this->assertInstanceOf(Builder::class, $result);
        // When user has viewAny permission, no where clause should be added
        $this->assertStringNotContainsString('where', strtolower($result->toSql()));
    }

    public function test_visible_to_scope_filters_by_user_id_when_no_view_any_permission(): void
    {
        $user = User::factory()->create();
        $testModel = new TestModelForVisibleToScope();
        
        // Mock Gate to deny viewAny
        Gate::shouldReceive('allows')
            ->once()
            ->with('viewAny', $testModel)
            ->andReturn(false);
        
        $result = $testModel->visibleTo($user);
        
        $this->assertInstanceOf(Builder::class, $result);
        // Should add where clause for user_id
        $this->assertStringContainsString('where', strtolower($result->toSql()));
        $this->assertStringContainsString('user_id', strtolower($result->toSql()));
    }

    public function test_visible_to_scope_uses_custom_column(): void
    {
        $user = User::factory()->create();
        $testModel = new TestModelForVisibleToScope();
        
        // Mock Gate to deny viewAny
        Gate::shouldReceive('allows')
            ->once()
            ->with('viewAny', $testModel)
            ->andReturn(false);
        
        $result = $testModel->visibleTo($user, 'owner_id');
        
        $this->assertInstanceOf(Builder::class, $result);
        // Should add where clause for the custom column
        $this->assertStringContainsString('where', strtolower($result->toSql()));
        $this->assertStringContainsString('owner_id', strtolower($result->toSql()));
    }

    public function test_visible_to_scope_with_actual_database_query(): void
    {
        // Create test users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        // Create test models with different user_ids
        $testModel1 = TestModelForVisibleToScope::create(['user_id' => $user1->id, 'name' => 'Test 1']);
        $testModel2 = TestModelForVisibleToScope::create(['user_id' => $user2->id, 'name' => 'Test 2']);
        
        // Mock Gate to deny viewAny for both tests
        Gate::shouldReceive('allows')
            ->with('viewAny', \Mockery::type(TestModelForVisibleToScope::class))
            ->andReturn(false);
        
        // Test that visibleTo only returns records for the specific user
        $results1 = TestModelForVisibleToScope::visibleTo($user1)->get();
        $results2 = TestModelForVisibleToScope::visibleTo($user2)->get();
        
        $this->assertCount(1, $results1);
        $this->assertCount(1, $results2);
        $this->assertEquals($testModel1->id, $results1->first()->id);
        $this->assertEquals($testModel2->id, $results2->first()->id);
    }

    public function test_filter_scope_with_actual_query_filter(): void
    {
        // Create test models
        TestModelForFilterScope::create(['name' => 'Apple']);
        TestModelForFilterScope::create(['name' => 'Banana']);
        TestModelForFilterScope::create(['name' => 'Cherry']);
        
        // Create a request with filter parameters
        $request = new Request(['name' => 'Apple']);
        $filter = new TestQueryFilter($request);
        
        // Test the filter scope
        $results = TestModelForFilterScope::filter($filter)->get();
        
        $this->assertCount(1, $results);
        $this->assertEquals('Apple', $results->first()->name);
    }

    public function test_static_includable_property_can_be_overridden(): void
    {
        // Test that child models can override the includable property
        $this->assertEquals(['testRelation'], TestModelWithIncludable::$includable);
    }
}

// Test model classes for testing the base Model functionality

class TestModelForFilterScope extends Model
{
    protected $table = 'test_models_for_filter_scope';
    protected $fillable = ['name'];
    public $timestamps = false;
    
    protected static function booted()
    {
        // Create the table if it doesn't exist (for testing purposes)
        if (!\Schema::hasTable('test_models_for_filter_scope')) {
            \Schema::create('test_models_for_filter_scope', function ($table) {
                $table->id();
                $table->string('name');
            });
        }
    }
}

class TestModelForVisibleToScope extends Model
{
    protected $table = 'test_models_for_visible_to_scope';
    protected $fillable = ['user_id', 'name'];
    public $timestamps = false;
    
    protected static function booted()
    {
        // Create the table if it doesn't exist (for testing purposes)
        if (!\Schema::hasTable('test_models_for_visible_to_scope')) {
            \Schema::create('test_models_for_visible_to_scope', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('name');
            });
        }
    }
}

class TestModelWithIncludable extends Model
{
    public static $includable = ['testRelation'];
    protected $table = 'test_models_with_includable';
    public $timestamps = false;
}

// Test QueryFilter for testing the filter scope
class TestQueryFilter extends QueryFilter
{
    public function name(string $value): Builder
    {
        return $this->builder->where('name', $value);
    }
}