<?php

namespace Tests\Unit\Requests\User;

use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateUserRequestTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'existing@example.com',
        ]);
    }

    /**
     * Create a properly configured UpdateUserRequest for testing.
     */
    protected function createRequest(): UpdateUserRequest
    {
        $request = UpdateUserRequest::create('/users/'.$this->user->id, 'PUT');
        $request->setRouteResolver(function () {
            $route = $this->mock(\Illuminate\Routing\Route::class);
            $route->shouldReceive('parameter')
                ->with('user', null)
                ->andReturn($this->user);

            return $route;
        });

        return $request;
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_authorizes_all_requests(): void
    {
        // Arrange
        $request = new UpdateUserRequest;

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

        $request = $this->createRequest();
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_partial_update_with_only_name(): void
    {
        // Arrange
        $data = [
            'name' => 'Updated Name',
        ];

        $request = $this->createRequest();
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_partial_update_with_only_email(): void
    {
        // Arrange
        $data = [
            'email' => 'newemail@example.com',
        ];

        $request = $this->createRequest();
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_partial_update_with_only_timezone(): void
    {
        // Arrange
        $data = [
            'default_timezone' => 'America/New_York',
        ];

        $request = $this->createRequest();
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_with_all_fields(): void
    {
        // Arrange
        $data = [
            'name' => 'John Doe Updated',
            'email' => 'updated@example.com',
            'default_timezone' => 'Asia/Tokyo',
        ];

        $request = $this->createRequest();
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_name_at_max_length(): void
    {
        // Arrange
        $data = [
            'name' => str_repeat('a', 100), // Exactly 100 characters
        ];

        $request = $this->createRequest();
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

        $request = $this->createRequest();
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_name_not_string(): void
    {
        // Arrange
        $data = [
            'name' => 12345,
        ];

        $request = $this->createRequest();
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_with_invalid_email_format(): void
    {
        // Arrange
        $data = [
            'email' => 'not-an-email',
        ];

        $request = $this->createRequest();
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_email_with_max_length(): void
    {
        // Arrange
        $localPart = str_repeat('a', 243);
        $email = $localPart.'@example.com'; // 255 chars total

        $data = [
            'email' => $email,
        ];

        $request = $this->createRequest();
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_email_exceeds_max_length(): void
    {
        // Arrange
        $email = str_repeat('a', 250).'@example.com'; // > 255 chars

        $data = [
            'email' => $email,
        ];

        $request = $this->createRequest();
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_email_already_exists(): void
    {
        // Arrange - Create another user with a different email
        User::factory()->create(['email' => 'other@example.com']);

        $data = [
            'email' => 'other@example.com', // Trying to change to existing email
        ];

        $request = $this->createRequest();
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_with_valid_timezones(): void
    {
        // Arrange
        $validTimezones = [
            'UTC',
            'America/New_York',
            'Europe/London',
            'Asia/Tokyo',
            'Australia/Sydney',
            'Pacific/Auckland',
        ];

        foreach ($validTimezones as $timezone) {
            $data = [
                'default_timezone' => $timezone,
            ];

            $request = $this->createRequest();
            $validator = Validator::make($data, $request->rules());

            // Assert
            $this->assertTrue($validator->passes(), "Failed for timezone: {$timezone}");
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_with_invalid_timezone(): void
    {
        // Arrange
        $data = [
            'default_timezone' => 'Invalid/Timezone',
        ];

        $request = $this->createRequest();
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('default_timezone', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_multiple_fields_together(): void
    {
        // Arrange
        $data = [
            'name' => 'Updated User Name',
            'email' => 'newemail@example.com',
            'default_timezone' => 'Europe/Paris',
        ];

        $request = $this->createRequest();
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_with_validator_method(): void
    {
        // Arrange
        $request = new UpdateUserRequest;

        // Act
        $reflection = new \ReflectionMethod($request, 'withValidator');

        // Assert
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('withValidator', $reflection->getName());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_expected_validation_rules_structure(): void
    {
        // Arrange
        $request = $this->createRequest();

        // Act
        $rules = $request->rules();

        // Assert
        $this->assertIsArray($rules);
        $this->assertCount(3, $rules);
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('default_timezone', $rules);

        // Verify 'sometimes' is present for all fields
        $this->assertContains('sometimes', $rules['name']);
        $this->assertContains('sometimes', $rules['email']);
        $this->assertContains('sometimes', $rules['default_timezone']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_extends_form_request(): void
    {
        // Arrange
        $request = new UpdateUserRequest;

        // Assert
        $this->assertInstanceOf(\Illuminate\Foundation\Http\FormRequest::class, $request);
    }
}
