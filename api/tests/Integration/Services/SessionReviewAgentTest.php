<?php

namespace Tests\Integration\Services;

use App\Ai\Agents\SessionReviewAgent;
use App\Models\Asset;
use App\Models\Org;
use App\Models\Request;
use App\Models\Session;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Enums\Lab;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SessionReviewAgentTest extends TestCase
{
    use RefreshDatabase;

    protected Org $org;

    protected User $user;

    protected Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Org::factory()->create();
        $this->user = User::factory()->create();
        $this->org->users()->attach($this->user->id);
        $this->asset = Asset::factory()->create(['org_id' => $this->org->id]);
    }

    #[Test]
    public function it_audits_session_with_faked_agent(): void
    {
        SessionReviewAgent::fake([
            [
                'ai_note' => 'All queries aligned with stated purpose.',
                'session_activity_risk' => 'low',
                'deviation_risk' => 'low',
                'overall_risk' => 'low',
                'flags' => [],
                'human_audit_confidence' => 20,
                'human_audit_required' => false,
            ],
        ]);

        $request = Request::factory()->create([
            'org_id' => $this->org->id,
            'requester_id' => $this->user->id,
            'asset_id' => $this->asset->id,
        ]);

        $session = Session::factory()->create([
            'org_id' => $this->org->id,
            'request_id' => $request->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->user->id,
        ]);

        $agent = new SessionReviewAgent($session, [
            'openai_metadata' => ['app' => 'ArgusPAM'],
        ]);
        $userPrompt = view('prompts.session-review.user', [
            'session' => $session,
        ])->render();

        $response = $agent->prompt($userPrompt);

        $this->assertEquals('All queries aligned with stated purpose.', $response['ai_note']);
        $this->assertEquals('low', $response['session_activity_risk']);
        $this->assertEquals([], $response['flags']);
        $this->assertFalse($response['human_audit_required']);

        SessionReviewAgent::assertPrompted(fn ($prompt) => $prompt->prompt === $userPrompt);
    }

    #[Test]
    public function it_includes_openai_metadata_in_provider_options_for_openai(): void
    {
        $request = Request::factory()->create([
            'org_id' => $this->org->id,
            'requester_id' => $this->user->id,
            'asset_id' => $this->asset->id,
        ]);

        $session = Session::factory()->create([
            'org_id' => $this->org->id,
            'request_id' => $request->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->user->id,
        ]);

        $agent = new SessionReviewAgent($session, [
            'openai_metadata' => ['app' => 'ArgusPAM'],
        ]);

        $this->assertSame(
            ['metadata' => ['app' => 'ArgusPAM']],
            $agent->providerOptions(Lab::OpenAI)
        );
        $this->assertSame([], $agent->providerOptions(Lab::Anthropic));
    }
}
