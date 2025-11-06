<?php

namespace Tests\Integration\Requests\User;

use App\Http\Requests\User\ChangePasswordRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ChangePasswordRequestTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('pam.password.min', 8);

        $this->user = User::factory()->create([
            'password' => Hash::make('current_password123'),
        ]);

        $this->actingAs($this->user);
    }

    /**
     * Create a properly configured ChangePasswordRequest for testing.
     */
    protected function createRequest(): ChangePasswordRequest
    {
        $request = ChangePasswordRequest::create('/user/change-password', 'POST');
        $request->setUserResolver(fn () => $this->user);

        return $request;
    }

    /**
     * Get rules without the current_password:api rule to avoid auth guard issues in unit tests.
     */
    protected function getRulesWithoutAuthGuard(ChangePasswordRequest $request): array
    {
        $rules = $request->rules();
        $rules['current_password'] = array_filter(
            $rules['current_password'],
            fn ($rule) => $rule !== 'current_password:api'
        );

        return $rules;
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_authorizes_all_requests(): void
    {
        // Arrange
        $request = new ChangePasswordRequest;

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
            'current_password' => 'current_password123',
            'new_password' => 'new_password456',
            'new_password_confirmation' => 'new_password456',
        ];

        $request = $this->createRequest();
        $validator = Validator::make($data, $this->getRulesWithoutAuthGuard($request));

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_current_password_missing(): void
    {
        // Arrange
        $data = [
            'new_password' => 'new_password456',
            'new_password_confirmation' => 'new_password456',
        ];

        $request = $this->createRequest();
        $validator = Validator::make($data, $this->getRulesWithoutAuthGuard($request));

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('current_password', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_new_password_missing(): void
    {
        // Arrange
        $data = [
            'current_password' => 'current_password123',
            'new_password_confirmation' => 'new_password456',
        ];

        $request = $this->createRequest();
        $validator = Validator::make($data, $this->getRulesWithoutAuthGuard($request));

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('new_password', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_new_password_confirmation_missing(): void
    {
        // Arrange
        $data = [
            'current_password' => 'current_password123',
            'new_password' => 'new_password456',
        ];

        $request = $this->createRequest();
        $validator = Validator::make($data, $this->getRulesWithoutAuthGuard($request));

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('new_password_confirmation', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_current_password_too_short(): void
    {
        // Arrange
        $data = [
            'current_password' => 'short1!', // 7 characters
            'new_password' => 'new_password456',
            'new_password_confirmation' => 'new_password456',
        ];

        $request = $this->createRequest();
        $validator = Validator::make($data, $this->getRulesWithoutAuthGuard($request));

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('current_password', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_new_password_too_short(): void
    {
        // Arrange
        $data = [
            'current_password' => 'current_password123',
            'new_password' => 'short1!', // 7 characters
            'new_password_confirmation' => 'short1!',
        ];

        $request = $this->createRequest();
        $validator = Validator::make($data, $this->getRulesWithoutAuthGuard($request));

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('new_password', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_passwords_at_minimum_length(): void
    {
        // Arrange
        $data = [
            'current_password' => 'current_password123',
            'new_password' => 'Pass123!', // Exactly 8 characters
            'new_password_confirmation' => 'Pass123!',
        ];

        $request = $this->createRequest();
        $validator = Validator::make($data, $this->getRulesWithoutAuthGuard($request));

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_passwords_dont_match(): void
    {
        // Arrange
        $data = [
            'current_password' => 'current_password123',
            'new_password' => 'new_password456',
            'new_password_confirmation' => 'different_password789',
        ];

        $request = $this->createRequest();
        $validator = Validator::make($data, $this->getRulesWithoutAuthGuard($request));

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('new_password', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_current_password_is_not_string(): void
    {
        // Arrange
        $data = [
            'current_password' => 12345,
            'new_password' => 'new_password456',
            'new_password_confirmation' => 'new_password456',
        ];

        $request = $this->createRequest();
        $validator = Validator::make($data, $this->getRulesWithoutAuthGuard($request));

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('current_password', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_new_password_is_not_string(): void
    {
        // Arrange
        $data = [
            'current_password' => 'current_password123',
            'new_password' => 12345,
            'new_password_confirmation' => 12345,
        ];

        $request = $this->createRequest();
        $validator = Validator::make($data, $this->getRulesWithoutAuthGuard($request));

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('new_password', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_loads_password_min_config(): void
    {
        // Arrange
        Config::set('pam.password.min', 12);

        // Act
        $request = $this->createRequest();
        $rules = $request->rules();

        // Assert
        $this->assertStringContainsString('min:12', $rules['current_password'][2]);
        $this->assertStringContainsString('min:12', $rules['new_password'][2]);
        $this->assertStringContainsString('min:12', $rules['new_password_confirmation'][2]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_with_long_passwords(): void
    {
        // Arrange
        $longPassword = str_repeat('a', 100);

        $data = [
            'current_password' => 'current_password123',
            'new_password' => $longPassword,
            'new_password_confirmation' => $longPassword,
        ];

        $request = $this->createRequest();
        $validator = Validator::make($data, $this->getRulesWithoutAuthGuard($request));

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_passwords_with_special_characters(): void
    {
        // Arrange
        $data = [
            'current_password' => 'current_password123',
            'new_password' => 'P@ssw0rd!#$%^&*()',
            'new_password_confirmation' => 'P@ssw0rd!#$%^&*()',
        ];

        $request = $this->createRequest();
        $validator = Validator::make($data, $this->getRulesWithoutAuthGuard($request));

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_current_password_validation_rule(): void
    {
        // Arrange
        $request = $this->createRequest();
        $rules = $request->rules();

        // Act & Assert
        $this->assertContains('current_password:api', $rules['current_password']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_confirmed_validation_for_new_password(): void
    {
        // Arrange
        $request = $this->createRequest();
        $rules = $request->rules();

        // Act & Assert
        $this->assertContains('confirmed:new_password_confirmation', $rules['new_password']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_custom_attributes(): void
    {
        // Arrange
        $request = new ChangePasswordRequest;

        // Act
        $attributes = $request->attributes();

        // Assert
        $this->assertIsArray($attributes);
        $this->assertArrayHasKey('current_password', $attributes);
        $this->assertArrayHasKey('new_password', $attributes);
        $this->assertArrayHasKey('new_password_confirmation', $attributes);
        $this->assertEquals('Current Password', $attributes['current_password']);
        $this->assertEquals('New Password', $attributes['new_password']);
        $this->assertEquals('New Password Confirmation', $attributes['new_password_confirmation']);
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
        $this->assertArrayHasKey('current_password', $rules);
        $this->assertArrayHasKey('new_password', $rules);
        $this->assertArrayHasKey('new_password_confirmation', $rules);

        // Verify all fields are required
        $this->assertContains('required', $rules['current_password']);
        $this->assertContains('required', $rules['new_password']);
        $this->assertContains('required', $rules['new_password_confirmation']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_extends_form_request(): void
    {
        // Arrange
        $request = new ChangePasswordRequest;

        // Assert
        $this->assertInstanceOf(\Illuminate\Foundation\Http\FormRequest::class, $request);
    }
}
