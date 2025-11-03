<?php

namespace Tests\Unit\Policies;

use App\Enums\AssetAccessRole;
use App\Models\Asset;
use App\Models\AssetAccessGrant;
use App\Models\Org;
use App\Models\Permission;
use App\Models\Request as RequestModel;
use App\Models\Role;
use App\Models\Session;
use App\Models\User;
use App\Policies\SessionPolicy;
use App\Services\OpenAI\OpenAiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class SessionPolicyTest extends TestCase
{
    use RefreshDatabase;

    private SessionPolicy $policy;
    private User $user;
    private User $requester;
    private User $approver;
    private Org $org;
    private Asset $asset;
    private RequestModel $request;
    private Session $session;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the OpenAI service to prevent API calls during tests
        $this->mock(OpenAiService::class, function ($mock) {
            $mock->shouldReceive('evaluateAccessRequest')->andReturn([
                'output_object' => (object) [
                    'aiNote' => 'Test AI evaluation',
                    'aiRiskRating' => 'low',
                ],
            ]);
        });

        $this->policy = new SessionPolicy;
        $this->user = User::factory()->create();
        $this->requester = User::factory()->create();
        $this->approver = User::factory()->create();
        $this->org = Org::factory()->create();
        $this->asset = Asset::factory()->create();
        $this->request = RequestModel::factory()->create();
        $this->session = Session::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'request_id' => $this->request->id,
            'requester_id' => $this->requester->id,
            'approver_id' => $this->approver->id,
            'start_datetime' => now(),
            'end_datetime' => now()->addHour(),
            'scheduled_start_datetime' => now(),
            'scheduled_end_datetime' => now()->addHour(),
            'requested_duration' => 60,
            'actual_duration' => 60,
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_view_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'session:view');

        $this->assertTrue($this->policy->view($this->user, $this->session));
    }

    public function test_view_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->view($this->user, $this->session));
    }

    public function test_permission_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'session:permission');

        $this->assertTrue($this->policy->permission($this->user, $this->session));
    }

    public function test_permission_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->permission($this->user, $this->session));
    }

    public function test_terminate_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'session:terminateany');

        $this->assertTrue($this->policy->terminateAny($this->user));
    }

    public function test_terminate_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->terminateAny($this->user));
    }

    public function test_terminate_returns_true_when_user_has_terminateany_permission(): void
    {
        $this->giveUserPermission($this->user, 'session:terminateany');

        $this->assertTrue($this->policy->terminate($this->user, $this->session));
    }

    public function test_terminate_returns_true_when_user_can_approve_and_is_not_requester(): void
    {
        $this->giveUserAssetAccess($this->user, $this->asset, AssetAccessRole::APPROVER);
        $this->giveUserPermission($this->user, 'session:terminate');

        $this->assertTrue($this->policy->terminate($this->user, $this->session));
    }

    public function test_terminate_returns_false_when_user_is_requester(): void
    {
        $this->giveUserAssetAccess($this->requester, $this->asset, AssetAccessRole::APPROVER);
        $this->giveUserPermission($this->requester, 'session:terminate');

        $this->assertFalse($this->policy->terminate($this->requester, $this->session));
    }

    public function test_retrieve_secret_returns_true_when_user_is_requester_with_permission(): void
    {
        $this->giveUserPermission($this->requester, 'session:retrievesecret');

        $this->assertTrue($this->policy->retrieveSecret($this->requester, $this->session));
    }

    public function test_retrieve_secret_returns_false_when_user_is_not_requester(): void
    {
        $this->giveUserPermission($this->user, 'session:retrievesecret');

        $this->assertFalse($this->policy->retrieveSecret($this->user, $this->session));
    }

    public function test_start_returns_true_when_user_is_requester_with_permission(): void
    {
        $this->giveUserPermission($this->requester, 'session:start');

        $this->assertTrue($this->policy->start($this->requester, $this->session));
    }

    public function test_start_returns_false_when_user_is_not_requester(): void
    {
        $this->giveUserPermission($this->user, 'session:start');

        $this->assertFalse($this->policy->start($this->user, $this->session));
    }

    public function test_end_returns_true_when_user_is_requester_with_permission(): void
    {
        $this->giveUserPermission($this->requester, 'session:end');

        $this->assertTrue($this->policy->end($this->requester, $this->session));
    }

    public function test_end_returns_false_when_user_is_not_requester(): void
    {
        $this->giveUserPermission($this->user, 'session:end');

        $this->assertFalse($this->policy->end($this->user, $this->session));
    }

    public function test_cancel_returns_true_when_user_is_requester_with_permission(): void
    {
        $this->giveUserPermission($this->requester, 'session:cancel');

        $this->assertTrue($this->policy->cancel($this->requester, $this->session));
    }

    public function test_cancel_returns_false_when_user_is_not_requester(): void
    {
        $this->giveUserPermission($this->user, 'session:cancel');

        $this->assertFalse($this->policy->cancel($this->user, $this->session));
    }

    public function test_requester_specific_actions(): void
    {
        $this->giveUserPermission($this->requester, 'session:retrievesecret');
        $this->giveUserPermission($this->requester, 'session:start');
        $this->giveUserPermission($this->requester, 'session:end');
        $this->giveUserPermission($this->requester, 'session:cancel');

        // Requester should be able to perform all actions
        $this->assertTrue($this->policy->retrieveSecret($this->requester, $this->session));
        $this->assertTrue($this->policy->start($this->requester, $this->session));
        $this->assertTrue($this->policy->end($this->requester, $this->session));
        $this->assertTrue($this->policy->cancel($this->requester, $this->session));

        // Other user with same permissions should not be able to perform these actions
        $this->giveUserPermission($this->user, 'session:retrievesecret');
        $this->giveUserPermission($this->user, 'session:start');
        $this->giveUserPermission($this->user, 'session:end');
        $this->giveUserPermission($this->user, 'session:cancel');

        $this->assertFalse($this->policy->retrieveSecret($this->user, $this->session));
        $this->assertFalse($this->policy->start($this->user, $this->session));
        $this->assertFalse($this->policy->end($this->user, $this->session));
        $this->assertFalse($this->policy->cancel($this->user, $this->session));
    }

    private function giveUserPermission(User $user, string $permissionName): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => $permissionName],
            ['description' => ucfirst(str_replace(':', ' ', $permissionName))]
        );
        $role = Role::factory()->create();
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);
        $user->clearUserRolePermissionCache();
    }

    private function giveUserAssetAccess(User $user, Asset $asset, AssetAccessRole $role): void
    {
        AssetAccessGrant::factory()->create([
            'asset_id' => $asset->id,
            'user_id' => $user->id,
            'role' => $role,
        ]);
    }
}
