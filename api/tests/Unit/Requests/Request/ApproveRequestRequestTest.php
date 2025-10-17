<?php

namespace Tests\Unit\Requests\Request;

use App\Enums\RequestScope;
use App\Enums\RiskRating;
use App\Http\Requests\Request\ApproveRequestRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ApproveRequestRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('pam.access_request.duration.min', 10);
        Config::set('pam.access_request.duration.max', 480);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_authorizes_all_requests(): void
    {
        // Arrange
        $request = new ApproveRequestRequest;

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
            'start_datetime' => Carbon::now()->addHour()->toDateTimeString(),
            'end_datetime' => Carbon::now()->addHours(3)->toDateTimeString(),
            'scope' => RequestScope::READ_ONLY->value,
            'approver_note' => 'Approved for maintenance window',
            'approver_risk_rating' => RiskRating::LOW->value,
        ];

        $request = new ApproveRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_start_datetime_missing(): void
    {
        // Arrange
        $data = [
            // 'start_datetime' => missing
            'end_datetime' => Carbon::now()->addHours(3)->toDateTimeString(),
            'scope' => RequestScope::READ_ONLY->value,
            'approver_note' => 'Test',
            'approver_risk_rating' => RiskRating::LOW->value,
        ];

        $request = new ApproveRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('start_datetime', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_end_datetime_missing(): void
    {
        // Arrange
        $data = [
            'start_datetime' => Carbon::now()->addHour()->toDateTimeString(),
            // 'end_datetime' => missing
            'scope' => RequestScope::READ_ONLY->value,
            'approver_note' => 'Test',
            'approver_risk_rating' => RiskRating::LOW->value,
        ];

        $request = new ApproveRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('end_datetime', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_scope_missing(): void
    {
        // Arrange
        $data = [
            'start_datetime' => Carbon::now()->addHour()->toDateTimeString(),
            'end_datetime' => Carbon::now()->addHours(3)->toDateTimeString(),
            // 'scope' => missing
            'approver_note' => 'Test',
            'approver_risk_rating' => RiskRating::LOW->value,
        ];

        $request = new ApproveRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('scope', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_approver_note_missing(): void
    {
        // Arrange
        $data = [
            'start_datetime' => Carbon::now()->addHour()->toDateTimeString(),
            'end_datetime' => Carbon::now()->addHours(3)->toDateTimeString(),
            'scope' => RequestScope::READ_ONLY->value,
            // 'approver_note' => missing
            'approver_risk_rating' => RiskRating::LOW->value,
        ];

        $request = new ApproveRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('approver_note', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_approver_risk_rating_missing(): void
    {
        // Arrange
        $data = [
            'start_datetime' => Carbon::now()->addHour()->toDateTimeString(),
            'end_datetime' => Carbon::now()->addHours(3)->toDateTimeString(),
            'scope' => RequestScope::READ_ONLY->value,
            'approver_note' => 'Test',
            // 'approver_risk_rating' => missing
        ];

        $request = new ApproveRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('approver_risk_rating', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_end_datetime_before_start(): void
    {
        // Arrange
        $data = [
            'start_datetime' => Carbon::now()->addHours(3)->toDateTimeString(),
            'end_datetime' => Carbon::now()->addHour()->toDateTimeString(), // Before start
            'scope' => RequestScope::READ_ONLY->value,
            'approver_note' => 'Test',
            'approver_risk_rating' => RiskRating::LOW->value,
        ];

        $request = new ApproveRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('end_datetime', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_end_datetime_in_past(): void
    {
        // Arrange
        $data = [
            'start_datetime' => Carbon::now()->subHours(2)->toDateTimeString(),
            'end_datetime' => Carbon::now()->subHour()->toDateTimeString(), // Past
            'scope' => RequestScope::READ_ONLY->value,
            'approver_note' => 'Test',
            'approver_risk_rating' => RiskRating::LOW->value,
        ];

        $request = new ApproveRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('end_datetime', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_duration_within_min_max_range(): void
    {
        // Arrange
        $data = [
            'start_datetime' => Carbon::now()->addHour()->toDateTimeString(),
            'end_datetime' => Carbon::now()->addHours(2)->toDateTimeString(),
            'duration' => 60, // Within range
            'scope' => RequestScope::READ_ONLY->value,
            'approver_note' => 'Test',
            'approver_risk_rating' => RiskRating::LOW->value,
        ];

        $request = new ApproveRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_duration_below_minimum(): void
    {
        // Arrange
        $data = [
            'start_datetime' => Carbon::now()->addHour()->toDateTimeString(),
            'end_datetime' => Carbon::now()->addHours(2)->toDateTimeString(),
            'duration' => 5, // Below min
            'scope' => RequestScope::READ_ONLY->value,
            'approver_note' => 'Test',
            'approver_risk_rating' => RiskRating::LOW->value,
        ];

        $request = new ApproveRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('duration', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_duration_exceeds_maximum(): void
    {
        // Arrange
        $data = [
            'start_datetime' => Carbon::now()->addHour()->toDateTimeString(),
            'end_datetime' => Carbon::now()->addHours(10)->toDateTimeString(),
            'duration' => 600, // Above max
            'scope' => RequestScope::READ_ONLY->value,
            'approver_note' => 'Test',
            'approver_risk_rating' => RiskRating::LOW->value,
        ];

        $request = new ApproveRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('duration', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_all_scope_enum_values(): void
    {
        // Arrange
        $scopes = [
            RequestScope::READ_ONLY,
            RequestScope::READ_WRITE,
            RequestScope::ALL,
        ];

        foreach ($scopes as $scope) {
            $data = [
                'start_datetime' => Carbon::now()->addHour()->toDateTimeString(),
                'end_datetime' => Carbon::now()->addHours(2)->toDateTimeString(),
                'scope' => $scope->value,
                'approver_note' => 'Test',
                'approver_risk_rating' => RiskRating::LOW->value,
            ];

            $request = new ApproveRequestRequest;
            $validator = Validator::make($data, $request->rules());

            // Assert
            $this->assertTrue($validator->passes(), "Failed for scope: {$scope->value}");
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_with_invalid_scope(): void
    {
        // Arrange
        $data = [
            'start_datetime' => Carbon::now()->addHour()->toDateTimeString(),
            'end_datetime' => Carbon::now()->addHours(2)->toDateTimeString(),
            'scope' => 'invalid_scope',
            'approver_note' => 'Test',
            'approver_risk_rating' => RiskRating::LOW->value,
        ];

        $request = new ApproveRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('scope', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_all_risk_rating_enum_values(): void
    {
        // Arrange
        $ratings = [
            RiskRating::LOW,
            RiskRating::MEDIUM,
            RiskRating::HIGH,
            RiskRating::CRITICAL,
        ];

        foreach ($ratings as $rating) {
            $data = [
                'start_datetime' => Carbon::now()->addHour()->toDateTimeString(),
                'end_datetime' => Carbon::now()->addHours(2)->toDateTimeString(),
                'scope' => RequestScope::READ_ONLY->value,
                'approver_note' => 'Test',
                'approver_risk_rating' => $rating->value,
            ];

            $request = new ApproveRequestRequest;
            $validator = Validator::make($data, $request->rules());

            // Assert
            $this->assertTrue($validator->passes(), "Failed for risk rating: {$rating->value}");
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_with_invalid_risk_rating(): void
    {
        // Arrange
        $data = [
            'start_datetime' => Carbon::now()->addHour()->toDateTimeString(),
            'end_datetime' => Carbon::now()->addHours(2)->toDateTimeString(),
            'scope' => RequestScope::READ_ONLY->value,
            'approver_note' => 'Test',
            'approver_risk_rating' => 'super_critical',
        ];

        $request = new ApproveRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('approver_risk_rating', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_custom_duration_min_error_message(): void
    {
        // Arrange
        $request = new ApproveRequestRequest;
        $messages = $request->messages();

        // Act & Assert
        $this->assertArrayHasKey('duration.min', $messages);
        $this->assertStringContainsString('at least', $messages['duration.min']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_custom_duration_max_error_message(): void
    {
        // Arrange
        $request = new ApproveRequestRequest;
        $messages = $request->messages();

        // Act & Assert
        $this->assertArrayHasKey('duration.max', $messages);
        $this->assertStringContainsString('less than', $messages['duration.max']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_custom_end_datetime_after_error_message(): void
    {
        // Arrange
        $request = new ApproveRequestRequest;
        $messages = $request->messages();

        // Act & Assert
        $this->assertArrayHasKey('end_datetime.after', $messages);
        $this->assertStringContainsString('must be after', $messages['end_datetime.after']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_loads_duration_config_in_constructor(): void
    {
        // Arrange
        Config::set('pam.access_request.duration.min', 20);
        Config::set('pam.access_request.duration.max', 720);

        // Act - Create request AFTER setting config
        $request = new ApproveRequestRequest;
        $rules = $request->rules();

        // Assert
        $this->assertStringContainsString('min:20', $rules['duration'][1]);
        $this->assertStringContainsString('max:720', $rules['duration'][2]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_extends_form_request(): void
    {
        // Arrange
        $request = new ApproveRequestRequest;

        // Assert
        $this->assertInstanceOf(\Illuminate\Foundation\Http\FormRequest::class, $request);
    }
}
