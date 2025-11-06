<?php

namespace Tests\Integration\Requests\User;

use App\Http\Requests\User\StoreUserRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreUserRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('pam.password.min', 8);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_authorizes_all_requests(): void
    {
        // Arrange
        $request = new StoreUserRequest;

        // Act
        $result = $request->authorize();

        // Assert
        $this->assertTrue($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_with_all_required_fields(): void
    {
        // Arrange
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'default_timezone' => 'UTC',
        ];

        $request = new StoreUserRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_name_missing(): void
    {
        // Arrange
        $data = [
            'email' => 'john@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'default_timezone' => 'UTC',
        ];

        $request = new StoreUserRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_email_missing(): void
    {
        // Arrange
        $data = [
            'name' => 'John Doe',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'default_timezone' => 'UTC',
        ];

        $request = new StoreUserRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_with_invalid_email_format(): void
    {
        // Arrange
        $data = [
            'name' => 'John Doe',
            'email' => 'not-an-email',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'default_timezone' => 'UTC',
        ];

        $request = new StoreUserRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_email_already_exists(): void
    {
        // Arrange
        User::factory()->create(['email' => 'existing@example.com']);

        $data = [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'default_timezone' => 'UTC',
        ];

        $request = new StoreUserRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_email_with_max_length(): void
    {
        // Arrange
        $localPart = str_repeat('a', 245); // 245 + @example.com = 257, but we need <= 255
        $email = substr($localPart, 0, 243).'@example.com'; // 255 chars total

        $data = [
            'name' => 'John Doe',
            'email' => $email,
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'default_timezone' => 'UTC',
        ];

        $request = new StoreUserRequest;
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
            'name' => 'John Doe',
            'email' => $email,
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'default_timezone' => 'UTC',
        ];

        $request = new StoreUserRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_password_missing(): void
    {
        // Arrange
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password_confirmation' => 'SecurePass123!',
            'default_timezone' => 'UTC',
        ];

        $request = new StoreUserRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_password_too_short(): void
    {
        // Arrange
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Short1!', // 7 characters
            'password_confirmation' => 'Short1!',
            'default_timezone' => 'UTC',
        ];

        $request = new StoreUserRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_password_at_minimum_length(): void
    {
        // Arrange
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Pass123!', // Exactly 8 characters
            'password_confirmation' => 'Pass123!',
            'default_timezone' => 'UTC',
        ];

        $request = new StoreUserRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_password_confirmation_missing(): void
    {
        // Arrange
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePass123!',
            'default_timezone' => 'UTC',
        ];

        $request = new StoreUserRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('password_confirmation', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_passwords_dont_match(): void
    {
        // Arrange
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'DifferentPass123!',
            'default_timezone' => 'UTC',
        ];

        $request = new StoreUserRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_timezone_missing(): void
    {
        // Arrange
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ];

        $request = new StoreUserRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('default_timezone', $validator->errors()->toArray());
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
        ];

        foreach ($validTimezones as $timezone) {
            $data = [
                'name' => 'John Doe',
                'email' => "john{$timezone}@example.com",
                'password' => 'SecurePass123!',
                'password_confirmation' => 'SecurePass123!',
                'default_timezone' => $timezone,
            ];

            $request = new StoreUserRequest;
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
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'default_timezone' => 'Invalid/Timezone',
        ];

        $request = new StoreUserRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('default_timezone', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_with_two_factor_enabled_true(): void
    {
        // Arrange
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'default_timezone' => 'UTC',
            'two_factor_enabled' => true,
        ];

        $request = new StoreUserRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_with_two_factor_enabled_false(): void
    {
        // Arrange
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'default_timezone' => 'UTC',
            'two_factor_enabled' => false,
        ];

        $request = new StoreUserRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_without_two_factor_enabled_field(): void
    {
        // Arrange - two_factor_enabled is optional with 'sometimes'
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'default_timezone' => 'UTC',
        ];

        $request = new StoreUserRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_two_factor_enabled_not_boolean(): void
    {
        // Arrange
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'default_timezone' => 'UTC',
            'two_factor_enabled' => 'yes',
        ];

        $request = new StoreUserRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('two_factor_enabled', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_name_at_max_length(): void
    {
        // Arrange
        $data = [
            'name' => str_repeat('a', 100), // Exactly 100 characters
            'email' => 'john@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'default_timezone' => 'UTC',
        ];

        $request = new StoreUserRequest;
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
            'email' => 'john@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'default_timezone' => 'UTC',
        ];

        $request = new StoreUserRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_loads_password_min_config(): void
    {
        // Arrange
        Config::set('pam.password.min', 12);

        // Act
        $request = new StoreUserRequest;
        $rules = $request->rules();

        // Assert
        $this->assertStringContainsString('min:12', $rules['password'][2]);
        $this->assertStringContainsString('min:12', $rules['password_confirmation'][2]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_custom_attributes(): void
    {
        // Arrange
        $request = new StoreUserRequest;

        // Act
        $attributes = $request->attributes();

        // Assert
        $this->assertIsArray($attributes);
        $this->assertArrayHasKey('name', $attributes);
        $this->assertArrayHasKey('email', $attributes);
        $this->assertArrayHasKey('password', $attributes);
        $this->assertArrayHasKey('password_confirmation', $attributes);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_extends_form_request(): void
    {
        // Arrange
        $request = new StoreUserRequest;

        // Assert
        $this->assertInstanceOf(\Illuminate\Foundation\Http\FormRequest::class, $request);
    }
}
