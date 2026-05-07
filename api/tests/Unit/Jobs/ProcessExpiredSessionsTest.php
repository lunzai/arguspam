<?php

namespace Tests\Unit\Jobs;

use App\Jobs\ProcessExpiredSessions;
use App\Models\Session;
use App\Services\Jit\Secrets\SecretsManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProcessExpiredSessionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    private function mockSecretsManager(): SecretsManager
    {
        return $this->mock(SecretsManager::class, function ($mock) {
            $mock->shouldReceive('terminateAccount')->andReturn(true);
            $mock->shouldReceive('cleanupExpiredAccounts')->andReturn(0);
        });
    }

    public function test_processes_started_sessions_past_scheduled_end(): void
    {
        $session = Session::factory()->create([
            'status' => 'started',
            'scheduled_end_datetime' => now()->subMinutes(10),
            'start_datetime' => now()->subHour(),
        ]);

        $secretsManager = $this->mockSecretsManager();
        app(ProcessExpiredSessions::class)->handle($secretsManager);

        $this->assertDatabaseHas('sessions', [
            'id' => $session->id,
            'status' => 'expired',
        ]);
    }

    public function test_does_not_process_sessions_not_yet_past_scheduled_end(): void
    {
        $session = Session::factory()->create([
            'status' => 'started',
            'scheduled_end_datetime' => now()->addHour(),
            'start_datetime' => now()->subMinutes(10),
        ]);

        $secretsManager = $this->mockSecretsManager();
        app(ProcessExpiredSessions::class)->handle($secretsManager);

        $this->assertDatabaseHas('sessions', [
            'id' => $session->id,
            'status' => 'started',
        ]);
    }

    public function test_does_not_process_scheduled_sessions(): void
    {
        $session = Session::factory()->create([
            'status' => 'scheduled',
            'scheduled_end_datetime' => now()->subMinutes(10),
        ]);

        $secretsManager = $this->mockSecretsManager();
        app(ProcessExpiredSessions::class)->handle($secretsManager);

        $this->assertDatabaseHas('sessions', [
            'id' => $session->id,
            'status' => 'scheduled',
        ]);
    }

    public function test_does_not_reprocess_already_expired_sessions(): void
    {
        $session = Session::factory()->create([
            'status' => 'expired',
            'scheduled_end_datetime' => now()->subMinutes(10),
        ]);

        $secretsManager = $this->mockSecretsManager();
        app(ProcessExpiredSessions::class)->handle($secretsManager);

        // Status should remain 'expired', not change
        $this->assertDatabaseHas('sessions', [
            'id' => $session->id,
            'status' => 'expired',
        ]);
    }
}
