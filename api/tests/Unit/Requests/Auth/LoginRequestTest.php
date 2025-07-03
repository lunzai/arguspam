<?php

namespace Tests\Unit\Requests\Auth;

use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class LoginRequestTest extends TestCase
{
    private LoginRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->request = new LoginRequest();
    }

    public function test_authorize_returns_true(): void
    {
        $this->assertTrue($this->request->authorize());
    }

    public function test_rules_returns_correct_validation_rules(): void
    {
        $expectedRules = [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:8'],
        ];
        
        $this->assertEquals($expectedRules, $this->request->rules());
    }

    public function test_validation_passes_with_valid_data(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_validation_fails_when_email_is_missing(): void
    {
        $data = [
            'password' => 'password123',
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        $this->assertContains('The email field is required.', $validator->errors()->get('email'));
    }

    public function test_validation_fails_when_email_is_invalid(): void
    {
        $data = [
            'email' => 'invalid-email',
            'password' => 'password123',
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        $this->assertContains('The email field must be a valid email address.', $validator->errors()->get('email'));
    }

    public function test_validation_fails_when_email_is_not_string(): void
    {
        $data = [
            'email' => 123,
            'password' => 'password123',
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_validation_fails_when_password_is_missing(): void
    {
        $data = [
            'email' => 'test@example.com',
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
        $this->assertContains('The password field is required.', $validator->errors()->get('password'));
    }

    public function test_validation_fails_when_password_is_too_short(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'short',
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
        $this->assertContains('The password field must be at least 8 characters.', $validator->errors()->get('password'));
    }

    public function test_validation_fails_when_password_is_not_string(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 12345678,
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_validation_passes_with_minimum_password_length(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => '12345678', // Exactly 8 characters
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_validation_passes_with_long_password(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'this_is_a_very_long_password_with_more_than_8_characters',
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_validation_passes_with_special_characters_in_email(): void
    {
        $data = [
            'email' => 'test+tag@sub.example.com',
            'password' => 'password123',
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_validation_fails_with_empty_strings(): void
    {
        $data = [
            'email' => '',
            'password' => '',
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_whitespace_only(): void
    {
        $data = [
            'email' => '   ',
            'password' => '   ',
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_extends_form_request(): void
    {
        $this->assertInstanceOf(\Illuminate\Foundation\Http\FormRequest::class, $this->request);
    }
}