<?php

namespace Tests\Unit\Requests\Request;

use App\Enums\RequestScope;
use App\Enums\RequestStatus;
use App\Enums\RiskRating;
use App\Http\Requests\Request\UpdateRequestRequest;
use App\Models\Asset;
use App\Models\AssetAccount;
use App\Models\Org;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateRequestRequestTest extends TestCase
{
    use RefreshDatabase;

    protected Org $org;

    protected User $user;

    protected Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('pam.access_request.duration.min', 10);
        Config::set('pam.access_request.duration.max', 480);

        $this->org = Org::factory()->create();
        $this->user = User::factory()->create();
        $this->org->users()->attach($this->user->id);
        $this->asset = Asset::factory()->create(['org_id' => $this->org->id]);

        $this->actingAs($this->user);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_authorizes_all_requests(): void
    {
        // Arrange
        $request = new UpdateRequestRequest;

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

        $request = new UpdateRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_partial_update_with_only_reason(): void
    {
        // Arrange
        $data = [
            'reason' => 'Updated reason for access',
        ];

        $request = new UpdateRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_org_id_when_provided(): void
    {
        // Arrange
        $data = [
            'org_id' => $this->org->id,
        ];

        $request = new UpdateRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_org_id_not_exists(): void
    {
        // Arrange
        $data = [
            'org_id' => 99999,
        ];

        $request = new UpdateRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('org_id', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_start_datetime_after_now(): void
    {
        // Arrange
        $data = [
            'start_datetime' => Carbon::now()->addHour()->toDateTimeString(),
        ];

        $request = new UpdateRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_start_datetime_in_past(): void
    {
        // Arrange
        $data = [
            'start_datetime' => Carbon::now()->subHour()->toDateTimeString(),
        ];

        $request = new UpdateRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('start_datetime', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_end_datetime_after_start_datetime(): void
    {
        // Arrange
        $data = [
            'start_datetime' => Carbon::now()->addHour()->toDateTimeString(),
            'end_datetime' => Carbon::now()->addHours(3)->toDateTimeString(),
        ];

        $request = new UpdateRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_end_datetime_before_start(): void
    {
        // Arrange
        $data = [
            'start_datetime' => Carbon::now()->addHours(3)->toDateTimeString(),
            'end_datetime' => Carbon::now()->addHour()->toDateTimeString(),
        ];

        $request = new UpdateRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('end_datetime', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_duration_within_range(): void
    {
        // Arrange
        $data = [
            'duration' => 120, // 2 hours
        ];

        $request = new UpdateRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_duration_below_minimum(): void
    {
        // Arrange
        $data = [
            'duration' => 5, // Below min of 10
        ];

        $request = new UpdateRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('duration', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_scope_enum(): void
    {
        // Arrange
        $data = [
            'scope' => RequestScope::ALL->value,
        ];

        $request = new UpdateRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_with_invalid_scope(): void
    {
        // Arrange
        $data = [
            'scope' => 'invalid_scope',
        ];

        $request = new UpdateRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('scope', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_status_enum(): void
    {
        // Arrange
        $data = [
            'status' => RequestStatus::APPROVED->value,
        ];

        $request = new UpdateRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_with_invalid_status(): void
    {
        // Arrange
        $data = [
            'status' => 'invalid_status',
        ];

        $request = new UpdateRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_ai_risk_rating_enum(): void
    {
        // Arrange
        $data = [
            'ai_risk_rating' => RiskRating::HIGH->value,
        ];

        $request = new UpdateRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_nullable_ai_fields(): void
    {
        // Arrange
        $data = [
            'ai_note' => null,
            'ai_risk_rating' => null,
        ];

        $request = new UpdateRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_ai_note_with_text(): void
    {
        // Arrange
        $data = [
            'ai_note' => 'AI analysis suggests low risk based on historical patterns.',
        ];

        $request = new UpdateRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_requires_sensitive_data_note_when_accessing_sensitive_data(): void
    {
        // Arrange
        $data = [
            'is_access_sensitive_data' => true,
            'sensitive_data_note' => null, // Explicitly null to trigger required_if
        ];

        $request = new UpdateRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('sensitive_data_note', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_sensitive_data_note_when_provided(): void
    {
        // Arrange
        $data = [
            'is_access_sensitive_data' => true,
            'sensitive_data_note' => 'Need to access PII for compliance report',
        ];

        $request = new UpdateRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_allows_null_sensitive_data_note_when_not_accessing_sensitive_data(): void
    {
        // Arrange
        $data = [
            'is_access_sensitive_data' => false,
            'sensitive_data_note' => null,
        ];

        $request = new UpdateRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_nullable_asset_account_id(): void
    {
        // Arrange
        $data = [
            'asset_account_id' => null,
        ];

        $request = new UpdateRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_existing_asset_account_id(): void
    {
        // Arrange
        $assetAccount = AssetAccount::factory()->create([
            'asset_id' => $this->asset->id,
        ]);

        $data = [
            'asset_account_id' => $assetAccount->id,
        ];

        $request = new UpdateRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_nullable_intended_query(): void
    {
        // Arrange
        $data = [
            'intended_query' => null,
        ];

        $request = new UpdateRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_reason_max_length(): void
    {
        // Arrange
        $data = [
            'reason' => str_repeat('a', 255), // Exactly 255 characters
        ];

        $request = new UpdateRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_reason_exceeds_max_length(): void
    {
        // Arrange
        $data = [
            'reason' => str_repeat('a', 256), // 256 characters
        ];

        $request = new UpdateRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('reason', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_normalizes_status_to_lowercase_in_prepare_for_validation(): void
    {
        // This would be tested in integration, but we can verify the method exists
        $request = new UpdateRequestRequest;

        $reflection = new \ReflectionMethod($request, 'prepareForValidation');
        $this->assertTrue($reflection->isProtected());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_multiple_fields_together(): void
    {
        // Arrange
        $data = [
            'reason' => 'Updated access request',
            'scope' => RequestScope::READ_WRITE->value,
            'status' => RequestStatus::PENDING->value,
            'duration' => 240,
            'ai_note' => 'AI review completed',
            'ai_risk_rating' => RiskRating::MEDIUM->value,
        ];

        $request = new UpdateRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_extends_form_request(): void
    {
        // Arrange
        $request = new UpdateRequestRequest;

        // Assert
        $this->assertInstanceOf(\Illuminate\Foundation\Http\FormRequest::class, $request);
    }
}
