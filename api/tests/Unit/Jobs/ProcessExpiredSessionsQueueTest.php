<?php

namespace Tests\Unit\Jobs;

use App\Jobs\ProcessExpiredSessions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProcessExpiredSessionsQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_can_be_dispatched_to_queue(): void
    {
        Queue::fake();

        // Dispatch the job
        ProcessExpiredSessions::dispatch();

        // Assert the job was pushed to the queue
        Queue::assertPushed(ProcessExpiredSessions::class);
    }

    public function test_job_can_be_dispatched_with_delay(): void
    {
        Queue::fake();

        // Dispatch the job with a delay
        ProcessExpiredSessions::dispatch()->delay(now()->addMinutes(5));

        // Assert the job was pushed to the queue
        Queue::assertPushed(ProcessExpiredSessions::class);
    }

    public function test_job_can_be_dispatched_on_specific_queue(): void
    {
        Queue::fake();

        // Dispatch the job to a specific queue
        ProcessExpiredSessions::dispatch()->onQueue('sessions');

        // Assert the job was pushed to the specific queue
        Queue::assertPushed(ProcessExpiredSessions::class, function ($job) {
            return $job->queue === 'sessions';
        });
    }

    public function test_job_can_be_dispatched_with_priority(): void
    {
        Queue::fake();

        // Dispatch the job with high priority
        ProcessExpiredSessions::dispatch()->onQueue('high');

        // Assert the job was pushed to the high priority queue
        Queue::assertPushed(ProcessExpiredSessions::class, function ($job) {
            return $job->queue === 'high';
        });
    }

    public function test_job_implements_should_queue_interface(): void
    {
        $job = new ProcessExpiredSessions;

        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $job);
    }

    public function test_job_has_correct_traits_for_queuing(): void
    {
        $job = new ProcessExpiredSessions;

        // Check for required traits
        $traits = class_uses($job);

        $this->assertArrayHasKey('Illuminate\Foundation\Bus\Dispatchable', $traits);
        $this->assertArrayHasKey('Illuminate\Queue\InteractsWithQueue', $traits);
        $this->assertArrayHasKey('Illuminate\Bus\Queueable', $traits);
        $this->assertArrayHasKey('Illuminate\Queue\SerializesModels', $traits);
    }

    public function test_job_can_be_serialized_for_queue(): void
    {
        $job = new ProcessExpiredSessions;

        // Test serialization (required for queuing)
        $serialized = serialize($job);
        $this->assertIsString($serialized);

        // Test deserialization
        $unserialized = unserialize($serialized);
        $this->assertInstanceOf(ProcessExpiredSessions::class, $unserialized);
    }

    public function test_job_has_no_constructor_parameters(): void
    {
        $reflection = new \ReflectionClass(ProcessExpiredSessions::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);
        $this->assertCount(0, $constructor->getParameters());
    }

    public function test_job_can_be_instantiated_multiple_times(): void
    {
        $job1 = new ProcessExpiredSessions;
        $job2 = new ProcessExpiredSessions;

        $this->assertInstanceOf(ProcessExpiredSessions::class, $job1);
        $this->assertInstanceOf(ProcessExpiredSessions::class, $job2);
        $this->assertNotSame($job1, $job2);
    }

    public function test_job_dispatch_returns_pending_dispatch(): void
    {
        $result = ProcessExpiredSessions::dispatch();

        $this->assertInstanceOf(\Illuminate\Foundation\Bus\PendingDispatch::class, $result);
    }

    public function test_job_can_be_chained(): void
    {
        Queue::fake();

        // Test job chaining
        ProcessExpiredSessions::dispatch()
            ->chain([
                ProcessExpiredSessions::class,
            ]);

        // Assert the job was pushed
        Queue::assertPushed(ProcessExpiredSessions::class);
    }

    public function test_job_can_be_dispatched_with_connection(): void
    {
        Queue::fake();

        // Dispatch the job to a specific connection
        ProcessExpiredSessions::dispatch()->onConnection('redis');

        // Assert the job was pushed
        Queue::assertPushed(ProcessExpiredSessions::class);
    }

    public function test_job_has_correct_class_name(): void
    {
        $this->assertEquals('ProcessExpiredSessions', class_basename(ProcessExpiredSessions::class));
    }

    public function test_job_has_correct_namespace(): void
    {
        $this->assertEquals('App\Jobs\ProcessExpiredSessions', ProcessExpiredSessions::class);
    }

    public function test_job_can_be_dispatched_synchronously(): void
    {
        Queue::fake();

        // Dispatch synchronously using dispatchSync
        ProcessExpiredSessions::dispatchSync();

        // Assert the job was pushed
        Queue::assertPushed(ProcessExpiredSessions::class);
    }

    public function test_job_can_be_cloned(): void
    {
        $originalJob = new ProcessExpiredSessions;
        $clonedJob = clone $originalJob;

        $this->assertInstanceOf(ProcessExpiredSessions::class, $clonedJob);
        $this->assertNotSame($originalJob, $clonedJob);
    }

    public function test_job_has_handle_method(): void
    {
        $job = new ProcessExpiredSessions;

        $this->assertTrue(method_exists($job, 'handle'));
        $this->assertTrue(is_callable([$job, 'handle']));
    }

    public function test_job_handle_method_is_public(): void
    {
        $reflection = new \ReflectionClass(ProcessExpiredSessions::class);
        $handleMethod = $reflection->getMethod('handle');

        $this->assertTrue($handleMethod->isPublic());
    }

    public function test_job_handle_method_returns_void(): void
    {
        $reflection = new \ReflectionClass(ProcessExpiredSessions::class);
        $handleMethod = $reflection->getMethod('handle');

        $this->assertEquals('void', $handleMethod->getReturnType()->getName());
    }

    public function test_job_handle_method_accepts_secrets_manager(): void
    {
        $reflection = new \ReflectionClass(ProcessExpiredSessions::class);
        $handleMethod = $reflection->getMethod('handle');
        $parameters = $handleMethod->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('secretsManager', $parameters[0]->getName());
        $this->assertEquals('App\Services\Secrets\SecretsManager', $parameters[0]->getType()->getName());
    }
}
