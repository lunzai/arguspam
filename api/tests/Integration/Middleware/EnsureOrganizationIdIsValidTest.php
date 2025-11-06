<?php

namespace Tests\Integration\Middleware;

use App\Http\Middleware\EnsureOrganizationIdIsValid;
use App\Models\Org;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class EnsureOrganizationIdIsValidTest extends TestCase
{
    use RefreshDatabase;

    private EnsureOrganizationIdIsValid $middleware;
    private User $user;
    private Org $org;

    protected function setUp(): void
    {
        parent::setUp();

        $this->middleware = new EnsureOrganizationIdIsValid;
        $this->user = User::factory()->create();
        $this->org = Org::factory()->create();
    }

    public function test_handle_returns_bad_request_when_organization_header_is_missing(): void
    {
        $request = Request::create('/test');
        $request->setUserResolver(function () {
            return $this->user;
        });

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Organization ID is required', $responseData['message']);
        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('organization', $responseData['errors']);
        $this->assertEquals(['Organization ID header is missing'], $responseData['errors']['organization']);
    }

    public function test_handle_returns_bad_request_when_organization_header_is_empty(): void
    {
        $request = Request::create('/test');
        $request->setUserResolver(function () {
            return $this->user;
        });
        $request->headers->set('x-organization-id', '');

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Organization ID is required', $responseData['message']);
        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('organization', $responseData['errors']);
        $this->assertEquals(['Organization ID header is missing'], $responseData['errors']['organization']);
    }

    public function test_handle_returns_bad_request_when_organization_header_is_null(): void
    {
        $request = Request::create('/test');
        $request->setUserResolver(function () {
            return $this->user;
        });
        $request->headers->set('x-organization-id', null);

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Organization ID is required', $responseData['message']);
        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('organization', $responseData['errors']);
        $this->assertEquals(['Organization ID header is missing'], $responseData['errors']['organization']);
    }

    public function test_handle_returns_forbidden_when_user_has_no_access_to_organization(): void
    {
        $request = Request::create('/test');
        $request->setUserResolver(function () {
            return $this->user;
        });
        $request->headers->set('x-organization-id', $this->org->id);

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Unauthorized access to organization', $responseData['message']);
        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('organization', $responseData['errors']);
        $this->assertEquals(['You do not have access to this organization'], $responseData['errors']['organization']);
    }

    public function test_handle_returns_forbidden_when_organization_id_does_not_exist(): void
    {
        $nonExistentOrgId = 99999;

        $request = Request::create('/test');
        $request->setUserResolver(function () {
            return $this->user;
        });
        $request->headers->set('x-organization-id', $nonExistentOrgId);

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Unauthorized access to organization', $responseData['message']);
        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('organization', $responseData['errors']);
        $this->assertEquals(['You do not have access to this organization'], $responseData['errors']['organization']);
    }

    public function test_handle_allows_access_when_user_has_access_to_organization(): void
    {
        // Give user access to organization
        $this->user->orgs()->attach($this->org);

        $request = Request::create('/test');
        $request->setUserResolver(function () {
            return $this->user;
        });
        $request->headers->set('x-organization-id', $this->org->id);

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"success":true}', $response->getContent());
    }

    public function test_handle_adds_organization_id_to_request_when_access_granted(): void
    {
        // Give user access to organization
        $this->user->orgs()->attach($this->org);

        $request = Request::create('/test');
        $request->setUserResolver(function () {
            return $this->user;
        });
        $request->headers->set('x-organization-id', $this->org->id);

        $capturedRequest = null;
        $this->middleware->handle($request, function ($req) use (&$capturedRequest) {
            $capturedRequest = $req;
            return response()->json(['success' => true]);
        });
        $this->assertNotNull($capturedRequest);
        $this->assertEquals($this->org->id, $capturedRequest?->get(config('pam.org.request_attribute')));
    }

    public function test_handle_with_multiple_organizations(): void
    {
        $org1 = Org::factory()->create();
        $org2 = Org::factory()->create();
        $org3 = Org::factory()->create();

        // Give user access to org1 and org2, but not org3
        $this->user->orgs()->attach([$org1->id, $org2->id]);

        // Test access to org1 - should pass
        $request1 = Request::create('/test');
        $request1->setUserResolver(function () {
            return $this->user;
        });
        $request1->headers->set('x-organization-id', $org1->id);

        $response1 = $this->middleware->handle($request1, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(200, $response1->getStatusCode());

        // Test access to org2 - should pass
        $request2 = Request::create('/test');
        $request2->setUserResolver(function () {
            return $this->user;
        });
        $request2->headers->set('x-organization-id', $org2->id);

        $response2 = $this->middleware->handle($request2, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(200, $response2->getStatusCode());

        // Test access to org3 - should fail
        $request3 = Request::create('/test');
        $request3->setUserResolver(function () {
            return $this->user;
        });
        $request3->headers->set('x-organization-id', $org3->id);

        $response3 = $this->middleware->handle($request3, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response3->getStatusCode());
    }

    public function test_handle_with_string_organization_id(): void
    {
        // Give user access to organization
        $this->user->orgs()->attach($this->org);

        $request = Request::create('/test');
        $request->setUserResolver(function () {
            return $this->user;
        });
        $request->headers->set('x-organization-id', (string) $this->org->id);

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"success":true}', $response->getContent());
    }

    public function test_handle_with_numeric_organization_id(): void
    {
        // Give user access to organization
        $this->user->orgs()->attach($this->org);

        $request = Request::create('/test');
        $request->setUserResolver(function () {
            return $this->user;
        });
        $request->headers->set('x-organization-id', $this->org->id);

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"success":true}', $response->getContent());
    }

    public function test_handle_with_invalid_organization_id_format(): void
    {
        $request = Request::create('/test');
        $request->setUserResolver(function () {
            return $this->user;
        });
        $request->headers->set('x-organization-id', 'invalid-id');

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Unauthorized access to organization', $responseData['message']);
        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('organization', $responseData['errors']);
        $this->assertEquals(['You do not have access to this organization'], $responseData['errors']['organization']);
    }

    public function test_handle_with_zero_organization_id(): void
    {
        $request = Request::create('/test');
        $request->setUserResolver(function () {
            return $this->user;
        });
        $request->headers->set('x-organization-id', '0');

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        // Zero is treated as empty/falsy by the middleware, so it returns BAD_REQUEST
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Organization ID is required', $responseData['message']);
        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('organization', $responseData['errors']);
        $this->assertEquals(['Organization ID header is missing'], $responseData['errors']['organization']);
    }

    public function test_handle_with_negative_organization_id(): void
    {
        $request = Request::create('/test');
        $request->setUserResolver(function () {
            return $this->user;
        });
        $request->headers->set('x-organization-id', '-1');

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Unauthorized access to organization', $responseData['message']);
        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('organization', $responseData['errors']);
        $this->assertEquals(['You do not have access to this organization'], $responseData['errors']['organization']);
    }

    public function test_handle_with_multiple_users_and_organizations(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $org1 = Org::factory()->create();
        $org2 = Org::factory()->create();

        // User 1 has access to org1, User 2 has access to org2
        $user1->orgs()->attach($org1);
        $user2->orgs()->attach($org2);

        // User 1 accessing org1 - should pass
        $request1 = Request::create('/test');
        $request1->setUserResolver(function () use ($user1) {
            return $user1;
        });
        $request1->headers->set('x-organization-id', $org1->id);

        $response1 = $this->middleware->handle($request1, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(200, $response1->getStatusCode());

        // User 1 accessing org2 - should fail
        $request2 = Request::create('/test');
        $request2->setUserResolver(function () use ($user1) {
            return $user1;
        });
        $request2->headers->set('x-organization-id', $org2->id);

        $response2 = $this->middleware->handle($request2, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response2->getStatusCode());

        // User 2 accessing org2 - should pass
        $request3 = Request::create('/test');
        $request3->setUserResolver(function () use ($user2) {
            return $user2;
        });
        $request3->headers->set('x-organization-id', $org2->id);

        $response3 = $this->middleware->handle($request3, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(200, $response3->getStatusCode());

        // User 2 accessing org1 - should fail
        $request4 = Request::create('/test');
        $request4->setUserResolver(function () use ($user2) {
            return $user2;
        });
        $request4->headers->set('x-organization-id', $org1->id);

        $response4 = $this->middleware->handle($request4, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response4->getStatusCode());
    }

    public function test_handle_preserves_existing_request_data(): void
    {
        // Give user access to organization
        $this->user->orgs()->attach($this->org);

        $request = Request::create('/test', 'POST', ['existing_param' => 'existing_value']);
        $request->setUserResolver(function () {
            return $this->user;
        });
        $request->headers->set('x-organization-id', $this->org->id);

        $capturedRequest = null;
        $this->middleware->handle($request, function ($req) use (&$capturedRequest) {
            $capturedRequest = $req;
            return response()->json(['success' => true]);
        });

        $this->assertNotNull($capturedRequest);
        $this->assertEquals('existing_value', $capturedRequest?->get('existing_param'));
        $this->assertEquals($this->org->id, $capturedRequest?->get(config('pam.org.request_attribute')));
    }

    public function test_handle_with_case_sensitive_header(): void
    {
        // Give user access to organization
        $this->user->orgs()->attach($this->org);

        $request = Request::create('/test');
        $request->setUserResolver(function () {
            return $this->user;
        });
        // Test with different case variations
        $request->headers->set('X-Organization-ID', $this->org->id);

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"success":true}', $response->getContent());
    }

    public function test_handle_with_uppercase_header(): void
    {
        // Give user access to organization
        $this->user->orgs()->attach($this->org);

        $request = Request::create('/test');
        $request->setUserResolver(function () {
            return $this->user;
        });
        $request->headers->set('X-ORGANIZATION-ID', $this->org->id);

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"success":true}', $response->getContent());
    }

    public function test_handle_verifies_organization_access_through_pivot_table(): void
    {
        $org1 = Org::factory()->create();
        $org2 = Org::factory()->create();

        // Attach user to org1 only
        $this->user->orgs()->attach($org1);

        // Verify user has access to org1
        $request1 = Request::create('/test');
        $request1->setUserResolver(function () {
            return $this->user;
        });
        $request1->headers->set('x-organization-id', $org1->id);

        $response1 = $this->middleware->handle($request1, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(200, $response1->getStatusCode());

        // Verify user does not have access to org2
        $request2 = Request::create('/test');
        $request2->setUserResolver(function () {
            return $this->user;
        });
        $request2->headers->set('x-organization-id', $org2->id);

        $response2 = $this->middleware->handle($request2, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response2->getStatusCode());
    }

    public function test_handle_uses_correct_config_values(): void
    {
        // This test ensures the middleware uses the correct config values
        $this->assertEquals('x-organization-id', config('pam.org.request_header'));
        $this->assertEquals('current_org_id', config('pam.org.request_attribute'));
    }

    public function test_handle_with_organization_id_as_float(): void
    {
        // Give user access to organization
        $this->user->orgs()->attach($this->org);

        $request = Request::create('/test');
        $request->setUserResolver(function () {
            return $this->user;
        });
        $request->headers->set('x-organization-id', (float) $this->org->id);

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"success":true}', $response->getContent());
    }

    public function test_handle_response_structure_consistency(): void
    {
        // Test that error responses have consistent structure
        $request = Request::create('/test');
        $request->setUserResolver(function () {
            return $this->user;
        });

        // Test missing header response structure
        $response1 = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $responseData1 = json_decode($response1->getContent(), true);
        $this->assertArrayHasKey('message', $responseData1);
        $this->assertArrayHasKey('errors', $responseData1);
        $this->assertArrayHasKey('organization', $responseData1['errors']);
        $this->assertIsArray($responseData1['errors']['organization']);

        // Test unauthorized access response structure
        $request->headers->set('x-organization-id', $this->org->id);
        $response2 = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $responseData2 = json_decode($response2->getContent(), true);
        $this->assertArrayHasKey('message', $responseData2);
        $this->assertArrayHasKey('errors', $responseData2);
        $this->assertArrayHasKey('organization', $responseData2['errors']);
        $this->assertIsArray($responseData2['errors']['organization']);
    }
}
