<?php

namespace Tests\Unit\Listeners;

use App\Ai\Agents\AccessRequestEvaluator;
use App\Events\RequestCreated;
use App\Listeners\HandleRequestCreated;
use App\Models\Asset;
use App\Models\Org;
use App\Models\Request;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HandleRequestCreatedTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_implements_should_queue(): void
    {
        $listener = new HandleRequestCreated;

        $this->assertInstanceOf(ShouldQueue::class, $listener);
    }

    #[Test]
    public function it_implements_should_be_encrypted(): void
    {
        $listener = new HandleRequestCreated;

        $this->assertInstanceOf(ShouldBeEncrypted::class, $listener);
    }

    #[Test]
    public function it_has_retry_properties(): void
    {
        $listener = new HandleRequestCreated;

        $this->assertEquals(3, $listener->tries);
        $this->assertEquals(5, $listener->backoff);
    }

    #[Test]
    public function it_evaluates_request_and_submits(): void
    {
        AccessRequestEvaluator::fake([
            [
                'ai_note' => 'Low risk evaluation.',
                'ai_risk_rating' => 'low',
            ],
        ]);

        $org = Org::factory()->create();
        $user = User::factory()->create();
        $org->users()->attach($user->id);
        $asset = Asset::factory()->create(['org_id' => $org->id]);

        $request = Request::withoutEvents(fn () => Request::factory()->create([
            'org_id' => $org->id,
            'requester_id' => $user->id,
            'asset_id' => $asset->id,
        ]));

        $event = new RequestCreated($request);
        $listener = new HandleRequestCreated;
        $listener->handle($event);

        $request->refresh();
        $this->assertEquals('Low risk evaluation.', $request->ai_note);
        $this->assertEquals('low', $request->ai_risk_rating->value);
        $this->assertEquals('submitted', $request->status->value);

        AccessRequestEvaluator::assertPrompted(fn ($prompt) => true);
    }
}
