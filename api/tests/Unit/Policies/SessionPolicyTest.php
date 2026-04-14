<?php

namespace Tests\Unit\Policies;

use App\Enums\AssetAccessRole;
use App\Enums\AssetAccountType;
use App\Enums\SessionStatus;
use App\Models\Asset;
use App\Models\AssetAccessGrant;
use App\Models\AssetAccount;
use App\Models\Org;
use App\Models\Session;
use App\Models\User;
use App\Policies\SessionPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SessionPolicyTest extends TestCase
{
    use RefreshDatabase;

    private SessionPolicy $policy;
    private User $user;
    private Org $org;
    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        $this->policy = new SessionPolicy;
        $this->org = Org::factory()->create();
        $this->user = User::factory()->create();
        $this->org->users()->attach($this->user->id);
        $this->asset = Asset::factory()->create(['org_id' => $this->org->id]);

        // Set org context so canApprove() scopes to the right org
        $this->app['request']->headers->set('x-organization-id', $this->org->id);
    }

    private function makeSession(array $overrides = []): Session
    {
        $account = AssetAccount::factory()->create([
            'asset_id' => $this->asset->id,
            'type' => AssetAccountType::JIT,
            'is_active' => true,
        ]);

        return Session::factory()->create(array_merge([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->user->id,
            'asset_account_id' => $account->id,
            'status' => SessionStatus::STARTED,
            'scheduled_start_datetime' => now()->subHour(),
            'scheduled_end_datetime' => now()->addHour(),
            'start_datetime' => now()->subMinutes(5),
        ], $overrides));
    }

    // -------------------------------------------------------------------------
    // view
    // -------------------------------------------------------------------------

    public function test_view_returns_true_with_permission(): void
    {
        $this->giveUserPermission($this->user, 'session:view');
        $this->assertTrue($this->policy->view($this->user));
    }

    public function test_view_returns_false_without_permission(): void
    {
        $this->assertFalse($this->policy->view($this->user));
    }

    // -------------------------------------------------------------------------
    // terminateAny
    // -------------------------------------------------------------------------

    public function test_terminate_any_returns_true_with_permission(): void
    {
        $this->giveUserPermission($this->user, 'session:terminateany');
        $this->assertTrue($this->policy->terminateAny($this->user));
    }

    // -------------------------------------------------------------------------
    // terminate
    // -------------------------------------------------------------------------

    public function test_terminate_returns_true_via_terminateany_shortcircuit(): void
    {
        $this->giveUserPermission($this->user, 'session:terminateany');
        $session = $this->makeSession(['requester_id' => User::factory()->create()->id]);
        $this->assertTrue($this->policy->terminate($this->user, $session));
    }

    public function test_terminate_returns_true_with_approve_permission_approver_grant_and_not_requester(): void
    {
        $approver = User::factory()->create();
        $this->org->users()->attach($approver->id);
        $this->giveUserPermission($approver, 'session:terminate');
        AssetAccessGrant::factory()->create([
            'asset_id' => $this->asset->id,
            'user_id' => $approver->id,
            'user_group_id' => null,
            'role' => AssetAccessRole::APPROVER,
        ]);

        // Session requester is $this->user, not $approver
        $session = $this->makeSession(['requester_id' => $this->user->id]);
        $this->app['request']->headers->set('x-organization-id', $this->org->id);

        $this->assertTrue($this->policy->terminate($approver, $session));
    }

    public function test_terminate_returns_false_when_is_requester(): void
    {
        $this->giveUserPermission($this->user, 'session:terminate');
        $session = $this->makeSession(['requester_id' => $this->user->id]);
        $this->assertFalse($this->policy->terminate($this->user, $session));
    }

    public function test_terminate_returns_false_with_terminate_permission_but_no_approver_grant(): void
    {
        $this->giveUserPermission($this->user, 'session:terminate');
        $session = $this->makeSession(['requester_id' => User::factory()->create()->id]);
        $this->assertFalse($this->policy->terminate($this->user, $session));
    }

    public function test_terminate_returns_false_without_permission(): void
    {
        $session = $this->makeSession(['requester_id' => User::factory()->create()->id]);
        $this->assertFalse($this->policy->terminate($this->user, $session));
    }

    // -------------------------------------------------------------------------
    // retrieveSecret
    // -------------------------------------------------------------------------

    public function test_retrieve_secret_returns_true_when_requester_with_permission(): void
    {
        $this->giveUserPermission($this->user, 'session:retrievesecret');
        $session = $this->makeSession(['requester_id' => $this->user->id]);
        $this->assertTrue($this->policy->retrieveSecret($this->user, $session));
    }

    public function test_retrieve_secret_returns_false_when_not_requester(): void
    {
        $this->giveUserPermission($this->user, 'session:retrievesecret');
        $session = $this->makeSession(['requester_id' => User::factory()->create()->id]);
        $this->assertFalse($this->policy->retrieveSecret($this->user, $session));
    }

    // -------------------------------------------------------------------------
    // start
    // -------------------------------------------------------------------------

    public function test_start_returns_true_when_requester_with_permission(): void
    {
        $this->giveUserPermission($this->user, 'session:start');
        $session = $this->makeSession(['requester_id' => $this->user->id]);
        $this->assertTrue($this->policy->start($this->user, $session));
    }

    public function test_start_returns_false_when_not_requester(): void
    {
        $this->giveUserPermission($this->user, 'session:start');
        $session = $this->makeSession(['requester_id' => User::factory()->create()->id]);
        $this->assertFalse($this->policy->start($this->user, $session));
    }

    // -------------------------------------------------------------------------
    // end
    // -------------------------------------------------------------------------

    public function test_end_returns_true_when_requester_with_permission(): void
    {
        $this->giveUserPermission($this->user, 'session:end');
        $session = $this->makeSession(['requester_id' => $this->user->id]);
        $this->assertTrue($this->policy->end($this->user, $session));
    }

    public function test_end_returns_false_when_not_requester(): void
    {
        $this->giveUserPermission($this->user, 'session:end');
        $session = $this->makeSession(['requester_id' => User::factory()->create()->id]);
        $this->assertFalse($this->policy->end($this->user, $session));
    }
}
