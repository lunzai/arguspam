<?php

namespace Tests\Unit\Traits;

use App\Enums\Status;
use App\Traits\HasStatus;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;

class HasStatusTest extends TestCase
{
    private TestStatusModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new TestStatusModel();
    }

    public function test_is_active_returns_true_when_status_enum_is_active(): void
    {
        $this->model->status = Status::ACTIVE;
        
        $this->assertTrue($this->model->isActive());
        $this->assertFalse($this->model->isInactive());
    }

    public function test_is_active_returns_false_when_status_enum_is_inactive(): void
    {
        $this->model->status = Status::INACTIVE;
        
        $this->assertFalse($this->model->isActive());
        $this->assertTrue($this->model->isInactive());
    }

    public function test_is_active_returns_true_when_status_string_is_active(): void
    {
        $this->model->status = Status::ACTIVE->value; // String value: 'active'
        
        $this->assertTrue($this->model->isActive());
        $this->assertFalse($this->model->isInactive());
    }

    public function test_is_active_returns_false_when_status_string_is_inactive(): void
    {
        $this->model->status = Status::INACTIVE->value; // String value: 'inactive'
        
        $this->assertFalse($this->model->isActive());
        $this->assertTrue($this->model->isInactive());
    }

    public function test_is_inactive_returns_true_when_status_enum_is_inactive(): void
    {
        $this->model->status = Status::INACTIVE;
        
        $this->assertTrue($this->model->isInactive());
        $this->assertFalse($this->model->isActive());
    }

    public function test_is_inactive_returns_false_when_status_enum_is_active(): void
    {
        $this->model->status = Status::ACTIVE;
        
        $this->assertFalse($this->model->isInactive());
        $this->assertTrue($this->model->isActive());
    }

    public function test_is_inactive_returns_true_when_status_string_is_inactive(): void
    {
        $this->model->status = Status::INACTIVE->value; // String value: 'inactive'
        
        $this->assertTrue($this->model->isInactive());
        $this->assertFalse($this->model->isActive());
    }

    public function test_is_inactive_returns_false_when_status_string_is_active(): void
    {
        $this->model->status = Status::ACTIVE->value; // String value: 'active'
        
        $this->assertFalse($this->model->isInactive());
        $this->assertTrue($this->model->isActive());
    }

    public function test_trait_uses_default_status_column(): void
    {
        $this->model->status = Status::ACTIVE;
        
        $this->assertTrue($this->model->isActive());
    }

    public function test_trait_works_with_custom_status_column(): void
    {
        $customModel = new TestStatusModelWithCustomColumn();
        $customModel->setStatusColumn('custom_status');
        $customModel->custom_status = Status::ACTIVE;
        
        $this->assertTrue($customModel->isActive());
        $this->assertFalse($customModel->isInactive());
    }

    public function test_trait_works_with_custom_status_column_string_values(): void
    {
        $customModel = new TestStatusModelWithCustomColumn();
        $customModel->setStatusColumn('custom_status');
        $customModel->custom_status = Status::INACTIVE->value;
        
        $this->assertTrue($customModel->isInactive());
        $this->assertFalse($customModel->isActive());
    }

    public function test_trait_handles_null_status(): void
    {
        $this->model->status = null;
        
        $this->assertFalse($this->model->isActive());
        $this->assertFalse($this->model->isInactive());
    }

    public function test_trait_handles_invalid_status_values(): void
    {
        $this->model->status = 'invalid_status';
        
        $this->assertFalse($this->model->isActive());
        $this->assertFalse($this->model->isInactive());
    }

    public function test_trait_methods_return_boolean(): void
    {
        $this->model->status = Status::ACTIVE;
        
        $this->assertIsBool($this->model->isActive());
        $this->assertIsBool($this->model->isInactive());
    }
}

// Test model using the HasStatus trait
class TestStatusModel extends Model
{
    use HasStatus;
    
    protected $table = 'test_status_models';
    public $timestamps = false;
    
    // Make status accessible for testing
    public $status;
}

// Test model with custom status column
class TestStatusModelWithCustomColumn extends Model
{
    use HasStatus;
    
    protected $table = 'test_status_models_custom';
    public $timestamps = false;
    
    // Make custom_status accessible for testing
    public $custom_status;
    
    public function setStatusColumn(string $column): void
    {
        $this->statusColumn = $column;
    }
}