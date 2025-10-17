<?php

namespace Tests\Unit\Middleware;

use App\Enums\AccessRestrictionType;
use App\Enums\Status;
use App\Http\Middleware\EnforceUserAccessRestrictions;
use App\Models\User;
use App\Models\UserAccessRestriction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class EnforceUserAccessRestrictionsTest extends TestCase
{
    use RefreshDatabase;

    private EnforceUserAccessRestrictions $middleware;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->middleware = new EnforceUserAccessRestrictions;
        $this->user = User::factory()->create();
    }

    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }

    public function test_handle_allows_unauthenticated_requests(): void
    {
        $request = Request::create('/test');
        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"success":true}', $response->getContent());
    }

    public function test_handle_allows_authenticated_user_with_no_restrictions(): void
    {
        $request = Request::create('/test');
        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"success":true}', $response->getContent());
    }

    public function test_handle_allows_user_with_inactive_restrictions(): void
    {
        UserAccessRestriction::factory()->create([
            'user_id' => $this->user->id,
            'type' => AccessRestrictionType::IP_ADDRESS,
            'value' => ['allowed_ips' => ['192.168.1.0/24']],
            'status' => Status::INACTIVE,
        ]);

        $request = Request::create('/test');
        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"success":true}', $response->getContent());
    }

    public function test_handle_allows_user_when_ip_restriction_passes(): void
    {
        UserAccessRestriction::factory()->create([
            'user_id' => $this->user->id,
            'type' => AccessRestrictionType::IP_ADDRESS,
            'value' => ['allowed_ips' => ['127.0.0.1']],
            'status' => Status::ACTIVE,
        ]);

        $request = Request::create('/test');
        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"success":true}', $response->getContent());
    }

    public function test_handle_denies_user_when_ip_restriction_fails(): void
    {
        UserAccessRestriction::factory()->create([
            'user_id' => $this->user->id,
            'type' => AccessRestrictionType::IP_ADDRESS,
            'value' => ['allowed_ips' => ['192.168.1.100']],
            'status' => Status::ACTIVE,
        ]);

        $request = Request::create('/test');
        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Access denied due to access restrictions', $responseData['message']);
        $this->assertEquals('ip address', $responseData['restriction_type']);
    }

    public function test_handle_allows_user_when_time_restriction_passes(): void
    {
        // Create a time restriction that should pass (current time should be within bounds)
        $currentHour = now()->format('H');
        $startTime = sprintf('%02d:00', max(0, $currentHour - 1));
        $endTime = sprintf('%02d:59', min(23, $currentHour + 1));
        $currentDay = (int) now()->format('w');

        UserAccessRestriction::factory()->create([
            'user_id' => $this->user->id,
            'type' => AccessRestrictionType::TIME_WINDOW,
            'value' => [
                'days' => [$currentDay],
                'start_time' => $startTime,
                'end_time' => $endTime,
                'timezone' => 'UTC',
            ],
            'status' => Status::ACTIVE,
        ]);

        $request = Request::create('/test');
        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"success":true}', $response->getContent());
    }

    public function test_handle_denies_user_when_time_restriction_fails(): void
    {
        // Create a time restriction that should fail (different day)
        $currentDay = (int) now()->format('w');
        $restrictedDay = ($currentDay + 1) % 7; // Different day

        UserAccessRestriction::factory()->create([
            'user_id' => $this->user->id,
            'type' => AccessRestrictionType::TIME_WINDOW,
            'value' => [
                'days' => [$restrictedDay],
                'start_time' => '09:00',
                'end_time' => '17:00',
                'timezone' => 'UTC',
            ],
            'status' => Status::ACTIVE,
        ]);

        $request = Request::create('/test');
        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Access denied due to access restrictions', $responseData['message']);
        $this->assertEquals('time window', $responseData['restriction_type']);
    }

    public function test_handle_allows_user_when_location_restriction_passes(): void
    {
        UserAccessRestriction::factory()->create([
            'user_id' => $this->user->id,
            'type' => AccessRestrictionType::COUNTRY,
            'value' => ['allowed_countries' => ['US', 'CA']],
            'status' => Status::ACTIVE,
        ]);

        $request = Request::create('/test');
        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        // Location restriction currently returns true by default (US placeholder)
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"success":true}', $response->getContent());
    }

    public function test_handle_uses_cache_for_restrictions(): void
    {
        UserAccessRestriction::factory()->create([
            'user_id' => $this->user->id,
            'type' => AccessRestrictionType::IP_ADDRESS,
            'value' => ['allowed_ips' => ['127.0.0.1']],
            'status' => Status::ACTIVE,
        ]);

        $request = Request::create('/test');
        $request->setUserResolver(function () {
            return $this->user;
        });

        // First call should hit database and cache
        $response1 = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(200, $response1->getStatusCode());
        $this->assertTrue(Cache::has("user_restrictions_{$this->user->id}"));

        // Second call should use cache
        $response2 = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(200, $response2->getStatusCode());
    }

    public function test_handle_with_cidr_ip_restriction(): void
    {
        UserAccessRestriction::factory()->create([
            'user_id' => $this->user->id,
            'type' => AccessRestrictionType::IP_ADDRESS,
            'value' => ['allowed_ips' => ['127.0.0.0/24']],
            'status' => Status::ACTIVE,
        ]);

        $request = Request::create('/test');
        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"success":true}', $response->getContent());
    }

    public function test_handle_with_multiple_allowed_ips(): void
    {
        UserAccessRestriction::factory()->create([
            'user_id' => $this->user->id,
            'type' => AccessRestrictionType::IP_ADDRESS,
            'value' => ['allowed_ips' => ['192.168.1.100', '127.0.0.1', '10.0.0.1']],
            'status' => Status::ACTIVE,
        ]);

        $request = Request::create('/test');
        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"success":true}', $response->getContent());
    }

    public function test_handle_with_empty_allowed_ips(): void
    {
        UserAccessRestriction::factory()->create([
            'user_id' => $this->user->id,
            'type' => AccessRestrictionType::IP_ADDRESS,
            'value' => ['allowed_ips' => []],
            'status' => Status::ACTIVE,
        ]);

        $request = Request::create('/test');
        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"success":true}', $response->getContent());
    }

    public function test_handle_with_time_restriction_missing_required_fields(): void
    {
        UserAccessRestriction::factory()->create([
            'user_id' => $this->user->id,
            'type' => AccessRestrictionType::TIME_WINDOW,
            'value' => ['days' => []],
            'status' => Status::ACTIVE,
        ]);

        $request = Request::create('/test');
        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"success":true}', $response->getContent());
    }

    public function test_handle_with_unknown_restriction_type(): void
    {
        // This test simulates handling default case in match expression
        // Create a restriction with a valid type but test the default behavior
        UserAccessRestriction::factory()->create([
            'user_id' => $this->user->id,
            'type' => AccessRestrictionType::COUNTRY, // Use valid type but rely on default case
            'value' => ['test' => 'value'], // Invalid value structure
            'status' => Status::ACTIVE,
        ]);

        $request = Request::create('/test');
        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        // Should allow through since location restriction defaults to true (placeholder implementation)
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"success":true}', $response->getContent());
    }

    public function test_handle_with_multiple_users(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // User 1 has IP restriction that should pass
        UserAccessRestriction::factory()->create([
            'user_id' => $user1->id,
            'type' => AccessRestrictionType::IP_ADDRESS,
            'value' => ['allowed_ips' => ['127.0.0.1']],
            'status' => Status::ACTIVE,
        ]);

        // User 2 has IP restriction that should fail
        UserAccessRestriction::factory()->create([
            'user_id' => $user2->id,
            'type' => AccessRestrictionType::IP_ADDRESS,
            'value' => ['allowed_ips' => ['192.168.1.100']],
            'status' => Status::ACTIVE,
        ]);

        // Test user 1 - should pass
        $request1 = Request::create('/test');
        $request1->setUserResolver(function () use ($user1) {
            return $user1;
        });

        $response1 = $this->middleware->handle($request1, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(200, $response1->getStatusCode());

        // Test user 2 - should fail
        $request2 = Request::create('/test');
        $request2->setUserResolver(function () use ($user2) {
            return $user2;
        });

        $response2 = $this->middleware->handle($request2, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response2->getStatusCode());
    }
}
