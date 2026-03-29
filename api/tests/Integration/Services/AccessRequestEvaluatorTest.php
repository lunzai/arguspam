<?php

namespace Tests\Integration\Services;

use App\Ai\Agents\AccessRequestEvaluator;
use App\Models\Asset;
use App\Models\Org;
use App\Models\Request;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Ai\Enums\Lab;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AccessRequestEvaluatorTest extends TestCase
{
    use RefreshDatabase;

    protected Org $org;

    protected User $user;

    protected Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('pam.openai', [
            'model' => 'gpt-4o-mini',
            'temperature' => 0.7,
            'metadata' => [
                'app' => 'ArgusPAM',
            ],
        ]);

        Config::set('pam.access_request.duration', [
            'min' => 1,
            'max' => 8,
            'recommended_min' => 2,
            'recommended_max' => 4,
            'low_threshold' => 240,
            'medium_threshold' => 1440,
            'high_threshold' => 10080,
        ]);

        $this->org = Org::factory()->create();
        $this->user = User::factory()->create();
        $this->org->users()->attach($this->user->id);
        $this->asset = Asset::factory()->create(['org_id' => $this->org->id]);
    }

    #[Test]
    public function it_evaluates_access_request_with_faked_agent(): void
    {
        AccessRequestEvaluator::fake([
            [
                'ai_note' => 'Low risk. Read-only access to non-sensitive data.',
                'ai_risk_rating' => 'low',
            ],
        ]);

        $request = Request::withoutEvents(fn () => Request::factory()->create([
            'org_id' => $this->org->id,
            'requester_id' => $this->user->id,
            'asset_id' => $this->asset->id,
        ]));

        $config = array_merge(config('pam.openai', []), config('pam.access_request.duration', []));
        $agent = new AccessRequestEvaluator($request, $config);
        $userPrompt = view('prompts.new-request.user', [
            'config' => $config,
            'request' => $request,
        ])->render();

        $response = $agent->prompt($userPrompt);

        $this->assertEquals('Low risk. Read-only access to non-sensitive data.', $response['ai_note']);
        $this->assertEquals('low', $response['ai_risk_rating']);

        AccessRequestEvaluator::assertPrompted(fn ($prompt) => $prompt->prompt === $userPrompt);
    }

    #[Test]
    public function it_includes_pam_openai_metadata_in_provider_options_for_openai(): void
    {
        $request = Request::factory()->create([
            'org_id' => $this->org->id,
            'requester_id' => $this->user->id,
            'asset_id' => $this->asset->id,
        ]);

        $agent = new AccessRequestEvaluator($request, []);

        $this->assertSame(
            ['metadata' => ['app' => 'ArgusPAM']],
            $agent->providerOptions(Lab::OpenAI)
        );
        $this->assertSame([], $agent->providerOptions(Lab::Anthropic));
    }
}
