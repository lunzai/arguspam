<?php

namespace Tests\Integration\Requests\Request;

use App\Enums\DatabaseScope;
use App\Http\Requests\Request\StoreRequestRequest;
use App\Models\Asset;
use App\Models\AssetAccount;
use App\Models\Org;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreRequestRequestTest extends TestCase
{
    use RefreshDatabase;

    protected Org $org;

    protected User $user;

    protected Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        // Set default duration config
        Config::set('pam.access_request.duration.min', 10);
        Config::set('pam.access_request.duration.max', 480); // 8 hours

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
        $request = new StoreRequestRequest;

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
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->user->id,
            'start_datetime' => Carbon::now()->addHour()->toDateTimeString(),
            'end_datetime' => Carbon::now()->addHours(2)->toDateTimeString(),
            'duration' => 60,
            'reason' => 'Need access for maintenance',
            'scope' => DatabaseScope::READ_ONLY->value,
            'is_access_sensitive_data' => false,
        ];

        $request = new StoreRequestRequest;
        $request->setContainer(app());
        $request->setUserResolver(fn () => $this->user);

        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_org_id_missing(): void
    {
        // Arrange
        $data = [
            // 'org_id' => missing
            'asset_id' => $this->asset->id,
            'requester_id' => $this->user->id,
            'start_datetime' => Carbon::now()->addHour()->toDateTimeString(),
            'end_datetime' => Carbon::now()->addHours(2)->toDateTimeString(),
            'reason' => 'Test',
            'scope' => DatabaseScope::READ_ONLY->value,
            'is_access_sensitive_data' => false,
        ];

        $request = new StoreRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('org_id', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_org_id_not_exists(): void
    {
        // Arrange
        $data = [
            'org_id' => 99999, // Non-existent
            'asset_id' => $this->asset->id,
            'requester_id' => $this->user->id,
            'start_datetime' => Carbon::now()->addHour()->toDateTimeString(),
            'end_datetime' => Carbon::now()->addHours(2)->toDateTimeString(),
            'reason' => 'Test',
            'scope' => DatabaseScope::READ_ONLY->value,
            'is_access_sensitive_data' => false,
        ];

        $request = new StoreRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('org_id', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_end_datetime_before_start(): void
    {
        // Arrange
        $data = [
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->user->id,
            'start_datetime' => Carbon::now()->addHours(2)->toDateTimeString(),
            'end_datetime' => Carbon::now()->addHour()->toDateTimeString(), // Before start
            'reason' => 'Test',
            'scope' => DatabaseScope::READ_ONLY->value,
            'is_access_sensitive_data' => false,
        ];

        $request = new StoreRequestRequest;
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
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->user->id,
            'start_datetime' => Carbon::now()->subHours(2)->toDateTimeString(),
            'end_datetime' => Carbon::now()->subHour()->toDateTimeString(), // In the past
            'reason' => 'Test',
            'scope' => DatabaseScope::READ_ONLY->value,
            'is_access_sensitive_data' => false,
        ];

        $request = new StoreRequestRequest;
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
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->user->id,
            'start_datetime' => Carbon::now()->addHour()->toDateTimeString(),
            'end_datetime' => Carbon::now()->addHours(2)->toDateTimeString(),
            'duration' => 60, // Within 10-480 range
            'reason' => 'Test',
            'scope' => DatabaseScope::READ_ONLY->value,
            'is_access_sensitive_data' => false,
        ];

        $request = new StoreRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_duration_below_minimum(): void
    {
        // Arrange
        $data = [
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->user->id,
            'start_datetime' => Carbon::now()->addHour()->toDateTimeString(),
            'end_datetime' => Carbon::now()->addHours(2)->toDateTimeString(),
            'duration' => 5, // Below min of 10
            'reason' => 'Test',
            'scope' => DatabaseScope::READ_ONLY->value,
            'is_access_sensitive_data' => false,
        ];

        $request = new StoreRequestRequest;
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
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->user->id,
            'start_datetime' => Carbon::now()->addHour()->toDateTimeString(),
            'end_datetime' => Carbon::now()->addHours(10)->toDateTimeString(),
            'duration' => 600, // Above max of 480
            'reason' => 'Test',
            'scope' => DatabaseScope::READ_ONLY->value,
            'is_access_sensitive_data' => false,
        ];

        $request = new StoreRequestRequest;
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
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->user->id,
            'start_datetime' => Carbon::now()->addHour()->toDateTimeString(),
            'end_datetime' => Carbon::now()->addHours(2)->toDateTimeString(),
            'reason' => 'Test',
            'scope' => DatabaseScope::READ_WRITE->value,
            'is_access_sensitive_data' => false,
        ];

        $request = new StoreRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_with_invalid_scope(): void
    {
        // Arrange
        $data = [
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->user->id,
            'start_datetime' => Carbon::now()->addHour()->toDateTimeString(),
            'end_datetime' => Carbon::now()->addHours(2)->toDateTimeString(),
            'reason' => 'Test',
            'scope' => 'invalid_scope',
            'is_access_sensitive_data' => false,
        ];

        $request = new StoreRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('scope', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_requires_sensitive_data_note_when_accessing_sensitive_data(): void
    {
        // Arrange
        $data = [
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->user->id,
            'start_datetime' => Carbon::now()->addHour()->toDateTimeString(),
            'end_datetime' => Carbon::now()->addHours(2)->toDateTimeString(),
            'reason' => 'Test',
            'scope' => DatabaseScope::READ_ONLY->value,
            'is_access_sensitive_data' => true,
            // Missing 'sensitive_data_note'
        ];

        $request = new StoreRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('sensitive_data_note', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_does_not_require_sensitive_data_note_when_not_accessing_sensitive_data(): void
    {
        // Arrange
        $data = [
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->user->id,
            'start_datetime' => Carbon::now()->addHour()->toDateTimeString(),
            'end_datetime' => Carbon::now()->addHours(2)->toDateTimeString(),
            'reason' => 'Test',
            'scope' => DatabaseScope::READ_ONLY->value,
            'is_access_sensitive_data' => false,
            // No 'sensitive_data_note' - should be fine
        ];

        $request = new StoreRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_optional_asset_account_id(): void
    {
        // Arrange
        $assetAccount = AssetAccount::factory()->create([
            'asset_id' => $this->asset->id,
        ]);

        $data = [
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'asset_account_id' => $assetAccount->id,
            'requester_id' => $this->user->id,
            'start_datetime' => Carbon::now()->addHour()->toDateTimeString(),
            'end_datetime' => Carbon::now()->addHours(2)->toDateTimeString(),
            'reason' => 'Test',
            'scope' => DatabaseScope::READ_ONLY->value,
            'is_access_sensitive_data' => false,
        ];

        $request = new StoreRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_optional_intended_query(): void
    {
        // Arrange
        $data = [
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->user->id,
            'start_datetime' => Carbon::now()->addHour()->toDateTimeString(),
            'end_datetime' => Carbon::now()->addHours(2)->toDateTimeString(),
            'reason' => 'Test',
            'intended_query' => 'SELECT * FROM users WHERE id = 1',
            'scope' => DatabaseScope::READ_ONLY->value,
            'is_access_sensitive_data' => false,
        ];

        $request = new StoreRequestRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_custom_duration_min_error_message(): void
    {
        // Arrange
        $request = new StoreRequestRequest;
        $messages = $request->messages();

        // Act & Assert
        $this->assertArrayHasKey('duration.min', $messages);
        $this->assertStringContainsString('at least', $messages['duration.min']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_custom_duration_max_error_message(): void
    {
        // Arrange
        $request = new StoreRequestRequest;
        $messages = $request->messages();

        // Act & Assert
        $this->assertArrayHasKey('duration.max', $messages);
        $this->assertStringContainsString('less than', $messages['duration.max']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_loads_duration_config_in_constructor(): void
    {
        // Arrange
        Config::set('pam.access_request.duration.min', 30);
        Config::set('pam.access_request.duration.max', 600);

        // Act - Create request AFTER setting config
        $request = new StoreRequestRequest;
        $rules = $request->rules();

        // Assert
        $this->assertStringContainsString('min:30', $rules['duration'][1]);
        $this->assertStringContainsString('max:600', $rules['duration'][2]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_extends_form_request(): void
    {
        // Arrange
        $request = new StoreRequestRequest;

        // Assert
        $this->assertInstanceOf(\Illuminate\Foundation\Http\FormRequest::class, $request);
    }
}
