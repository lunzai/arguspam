<?php

namespace Tests\Unit\Requests\Session;

use App\Http\Requests\Session\UpdateSessionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateSessionRequestTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_does_not_authorize_requests(): void
    {
        // Arrange
        $request = new UpdateSessionRequest;

        // Act
        $result = $request->authorize();

        // Assert
        $this->assertFalse($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_with_empty_data(): void
    {
        // Arrange - session_note is optional with 'sometimes'
        $data = [];

        $request = new UpdateSessionRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_with_session_note(): void
    {
        // Arrange
        $data = [
            'session_note' => 'User completed maintenance tasks successfully',
        ];

        $request = new UpdateSessionRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_with_null_session_note(): void
    {
        // Arrange
        $data = [
            'session_note' => null,
        ];

        $request = new UpdateSessionRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_with_empty_string_session_note(): void
    {
        // Arrange
        $data = [
            'session_note' => '',
        ];

        $request = new UpdateSessionRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_session_note_at_max_length(): void
    {
        // Arrange
        $data = [
            'session_note' => str_repeat('a', 255), // Exactly 255 characters
        ];

        $request = new UpdateSessionRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_session_note_exceeds_max_length(): void
    {
        // Arrange
        $data = [
            'session_note' => str_repeat('a', 256), // 256 characters
        ];

        $request = new UpdateSessionRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('session_note', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_session_note_is_not_string(): void
    {
        // Arrange
        $data = [
            'session_note' => 12345,
        ];

        $request = new UpdateSessionRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('session_note', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_with_multiline_session_note(): void
    {
        // Arrange
        $data = [
            'session_note' => "Session summary:\n- Accessed database\n- Ran queries\n- Completed task",
        ];

        $request = new UpdateSessionRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_with_special_characters_in_session_note(): void
    {
        // Arrange
        $data = [
            'session_note' => 'Query: SELECT * FROM users WHERE id = 1; @timestamp: 2025-01-01',
        ];

        $request = new UpdateSessionRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_only_one_validation_rule(): void
    {
        // Arrange
        $request = new UpdateSessionRequest;
        $rules = $request->rules();

        // Act & Assert
        $this->assertCount(1, $rules);
        $this->assertArrayHasKey('session_note', $rules);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_extends_form_request(): void
    {
        // Arrange
        $request = new UpdateSessionRequest;

        // Assert
        $this->assertInstanceOf(\Illuminate\Foundation\Http\FormRequest::class, $request);
    }
}
