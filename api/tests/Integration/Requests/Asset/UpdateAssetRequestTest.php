<?php

namespace Tests\Integration\Requests\Asset;

use App\Enums\Status;
use App\Http\Requests\Asset\UpdateAssetRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateAssetRequestTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_authorizes_all_requests(): void
    {
        // Arrange
        $request = new UpdateAssetRequest;

        // Act
        $result = $request->authorize();

        // Assert
        $this->assertTrue($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_with_empty_data(): void
    {
        // Arrange - All fields are optional with 'sometimes'
        $data = [];

        $request = new UpdateAssetRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_partial_update_with_only_name(): void
    {
        // Arrange
        $data = [
            'name' => 'Updated Asset Name',
        ];

        $request = new UpdateAssetRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_partial_update_with_only_description(): void
    {
        // Arrange
        $data = [
            'description' => 'Updated description for this asset',
        ];

        $request = new UpdateAssetRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_partial_update_with_only_status(): void
    {
        // Arrange
        $data = [
            'status' => Status::INACTIVE->value,
        ];

        $request = new UpdateAssetRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_with_all_fields(): void
    {
        // Arrange
        $data = [
            'name' => 'Production Database Updated',
            'description' => 'Main production database - updated',
            'status' => Status::ACTIVE->value,
        ];

        $request = new UpdateAssetRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_name_within_length_constraints(): void
    {
        // Arrange
        $data = [
            'name' => 'DB', // Min 2 characters
        ];

        $request = new UpdateAssetRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_name_too_short(): void
    {
        // Arrange
        $data = [
            'name' => 'D', // Only 1 character
        ];

        $request = new UpdateAssetRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_name_at_max_length(): void
    {
        // Arrange
        $data = [
            'name' => str_repeat('a', 100), // Exactly 100 characters
        ];

        $request = new UpdateAssetRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_name_exceeds_max_length(): void
    {
        // Arrange
        $data = [
            'name' => str_repeat('a', 101), // 101 characters
        ];

        $request = new UpdateAssetRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_nullable_description(): void
    {
        // Arrange
        $data = [
            'description' => null,
        ];

        $request = new UpdateAssetRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_with_long_description(): void
    {
        // Arrange
        $data = [
            'description' => str_repeat('This is a very long description. ', 100),
        ];

        $request = new UpdateAssetRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_all_status_enum_values(): void
    {
        // Arrange
        $statuses = [Status::ACTIVE, Status::INACTIVE];

        foreach ($statuses as $status) {
            $data = [
                'status' => $status->value,
            ];

            $request = new UpdateAssetRequest;
            $validator = Validator::make($data, $request->rules());

            // Assert
            $this->assertTrue($validator->passes(), "Failed for status: {$status->value}");
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_with_invalid_status(): void
    {
        // Arrange
        $data = [
            'status' => 'unknown_status',
        ];

        $request = new UpdateAssetRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_normalizes_status_to_lowercase_in_prepare_for_validation(): void
    {
        // Arrange
        $request = UpdateAssetRequest::create('/assets/1', 'PUT', [
            'status' => 'ACTIVE',
        ]);

        // Act
        $reflection = new \ReflectionMethod($request, 'prepareForValidation');
        $reflection->setAccessible(true);
        $reflection->invoke($request);

        // Assert
        $this->assertEquals('active', $request->input('status'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_does_not_modify_status_when_not_present(): void
    {
        // Arrange
        $request = UpdateAssetRequest::create('/assets/1', 'PUT', [
            'name' => 'Test Asset',
        ]);

        // Act
        $reflection = new \ReflectionMethod($request, 'prepareForValidation');
        $reflection->setAccessible(true);
        $reflection->invoke($request);

        // Assert
        $this->assertNull($request->input('status'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_attributes_from_model(): void
    {
        // Arrange
        $request = new UpdateAssetRequest;

        // Act
        $attributes = $request->attributes();

        // Assert
        $this->assertIsArray($attributes);
        // Verify it's using Asset::$attributeLabels
        $this->assertNotEmpty($attributes);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_expected_validation_rules_structure(): void
    {
        // Arrange
        $request = new UpdateAssetRequest;

        // Act
        $rules = $request->rules();

        // Assert
        $this->assertIsArray($rules);
        $this->assertCount(3, $rules);
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('description', $rules);
        $this->assertArrayHasKey('status', $rules);

        // Verify 'sometimes' is present for all fields
        $this->assertContains('sometimes', $rules['name']);
        $this->assertContains('sometimes', $rules['description']);
        $this->assertContains('sometimes', $rules['status']);

        // Verify description is nullable
        $this->assertContains('nullable', $rules['description']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_extends_form_request(): void
    {
        // Arrange
        $request = new UpdateAssetRequest;

        // Assert
        $this->assertInstanceOf(\Illuminate\Foundation\Http\FormRequest::class, $request);
    }
}
