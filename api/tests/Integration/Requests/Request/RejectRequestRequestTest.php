<?php

namespace Tests\Integration\Requests\Request;

use App\Http\Requests\Request\RejectRequestRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class RejectRequestRequestTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_authorizes_all_requests(): void
    {
        // Arrange
        $request = new RejectRequestRequest;

        // Act
        $result = $request->authorize();

        // Assert
        $this->assertTrue($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_with_required_approver_note(): void
    {
        // Arrange
        $data = [
            'approver_note' => 'Request rejected due to insufficient justification',
        ];

        $request = new RejectRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_approver_note_missing(): void
    {
        // Arrange
        $data = [];

        $request = new RejectRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('approver_note', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_approver_note_is_null(): void
    {
        // Arrange
        $data = [
            'approver_note' => null,
        ];

        $request = new RejectRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('approver_note', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_approver_note_is_empty_string(): void
    {
        // Arrange
        $data = [
            'approver_note' => '',
        ];

        $request = new RejectRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('approver_note', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_approver_note_is_not_string(): void
    {
        // Arrange
        $data = [
            'approver_note' => 12345,
        ];

        $request = new RejectRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('approver_note', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_with_short_approver_note(): void
    {
        // Arrange
        $data = [
            'approver_note' => 'No',
        ];

        $request = new RejectRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_with_long_approver_note(): void
    {
        // Arrange
        $data = [
            'approver_note' => str_repeat('This request is rejected because ', 100),
        ];

        $request = new RejectRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_with_multiline_approver_note(): void
    {
        // Arrange
        $data = [
            'approver_note' => "Rejected for the following reasons:\n1. Insufficient justification\n2. Security concerns\n3. Timing not appropriate",
        ];

        $request = new RejectRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_with_special_characters_in_approver_note(): void
    {
        // Arrange
        $data = [
            'approver_note' => 'Rejected! @#$%^&*() - Access denied due to policy violation.',
        ];

        $request = new RejectRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_only_one_validation_rule(): void
    {
        // Arrange
        $request = new RejectRequestRequest;
        $rules = $request->rules();

        // Act & Assert
        $this->assertCount(1, $rules);
        $this->assertArrayHasKey('approver_note', $rules);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_extends_form_request(): void
    {
        // Arrange
        $request = new RejectRequestRequest;

        // Assert
        $this->assertInstanceOf(\Illuminate\Foundation\Http\FormRequest::class, $request);
    }
}
