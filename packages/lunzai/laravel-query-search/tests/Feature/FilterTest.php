<?php

namespace Lunzai\QuerySearch\Tests\Feature;

use Illuminate\Http\Request;
use Lunzai\QuerySearch\Tests\Fixtures\Models\Role;
use Lunzai\QuerySearch\Tests\Fixtures\Models\User;
use Lunzai\QuerySearch\Tests\Fixtures\Search\UserSearch;
use Lunzai\QuerySearch\Tests\TestCase;

class FilterTest extends TestCase
{
    public function test_filter_by_exact_status(): void
    {
        User::create(['name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'active']);
        User::create(['name' => 'Bob',   'email' => 'bob@example.com',   'status' => 'inactive']);

        $result = (new UserSearch)
            ->for(User::query())
            ->fromRequest(Request::create('/', 'GET', ['filter' => ['status' => 'active']]))
            ->paginate(10);

        $this->assertCount(1, $result);
        $this->assertEquals('alice@example.com', $result->first()->email);
    }

    public function test_filter_by_in_multiple_statuses(): void
    {
        User::create(['name' => 'Alice',   'email' => 'alice@example.com',   'status' => 'active']);
        User::create(['name' => 'Bob',     'email' => 'bob@example.com',     'status' => 'inactive']);
        User::create(['name' => 'Charlie', 'email' => 'charlie@example.com', 'status' => 'suspended']);

        $result = (new UserSearch)
            ->for(User::query())
            ->fromRequest(Request::create('/', 'GET', ['filter' => ['status' => 'active,inactive']]))
            ->paginate(10);

        $this->assertCount(2, $result);
    }

    public function test_filter_by_email_like(): void
    {
        User::create(['name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'active']);
        User::create(['name' => 'Bob',   'email' => 'bob@example.com',   'status' => 'active']);

        $result = (new UserSearch)
            ->for(User::query())
            ->fromRequest(Request::create('/', 'GET', ['filter' => ['email' => 'alice']]))
            ->paginate(10);

        $this->assertCount(1, $result);
        $this->assertEquals('alice@example.com', $result->first()->email);
    }

    public function test_filter_by_date_range(): void
    {
        User::create(['name' => 'Old',  'email' => 'old@example.com',  'status' => 'active', 'created_at' => '2023-06-01']);
        User::create(['name' => 'New',  'email' => 'new@example.com',  'status' => 'active', 'created_at' => '2024-06-01']);
        User::create(['name' => 'Newer', 'email' => 'newer@example.com', 'status' => 'active', 'created_at' => '2025-01-01']);

        $result = (new UserSearch)
            ->for(User::query())
            ->fromRequest(Request::create('/', 'GET', ['filter' => ['created_at' => '2024-01-01,2024-12-31']]))
            ->paginate(10);

        $this->assertCount(1, $result);
        $this->assertEquals('new@example.com', $result->first()->email);
    }

    public function test_filter_by_date_range_min_only(): void
    {
        User::create(['name' => 'Old', 'email' => 'old@example.com', 'status' => 'active', 'created_at' => '2023-06-01']);
        User::create(['name' => 'New', 'email' => 'new@example.com', 'status' => 'active', 'created_at' => '2024-06-01']);

        $result = (new UserSearch)
            ->for(User::query())
            ->fromRequest(Request::create('/', 'GET', ['filter' => ['created_at' => '2024-01-01,']]))
            ->paginate(10);

        $this->assertCount(1, $result);
        $this->assertEquals('new@example.com', $result->first()->email);
    }

    public function test_filter_by_relation_role_id(): void
    {
        $adminRole = Role::create(['name' => 'Admin']);
        $userRole = Role::create(['name' => 'User']);

        $admin = User::create(['name' => 'Admin User', 'email' => 'admin@example.com', 'status' => 'active']);
        $plain = User::create(['name' => 'Plain User', 'email' => 'plain@example.com', 'status' => 'active']);

        $admin->roles()->attach($adminRole);
        $plain->roles()->attach($userRole);

        $result = (new UserSearch)
            ->for(User::query())
            ->fromRequest(Request::create('/', 'GET', ['filter' => ['role_id' => (string) $adminRole->id]]))
            ->paginate(10);

        $this->assertCount(1, $result);
        $this->assertEquals('admin@example.com', $result->first()->email);
    }

    public function test_filter_by_relation_multiple_role_ids(): void
    {
        $adminRole = Role::create(['name' => 'Admin']);
        $modRole = Role::create(['name' => 'Moderator']);
        $userRole = Role::create(['name' => 'User']);

        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'status' => 'active']);
        $mod = User::create(['name' => 'Mod',   'email' => 'mod@example.com',   'status' => 'active']);
        $plain = User::create(['name' => 'Plain', 'email' => 'plain@example.com', 'status' => 'active']);

        $admin->roles()->attach($adminRole);
        $mod->roles()->attach($modRole);
        $plain->roles()->attach($userRole);

        $ids = $adminRole->id.','.$modRole->id;

        $result = (new UserSearch)
            ->for(User::query())
            ->fromRequest(Request::create('/', 'GET', ['filter' => ['role_id' => $ids]]))
            ->paginate(10);

        $this->assertCount(2, $result);
    }

    public function test_filter_mfa_active(): void
    {
        User::create(['name' => 'Active2FA', 'email' => 'active@example.com', 'status' => 'active',
            'two_factor_enabled' => true, 'two_factor_confirmed_at' => now()]);
        User::create(['name' => 'Pending2FA', 'email' => 'pending@example.com', 'status' => 'active',
            'two_factor_enabled' => true, 'two_factor_confirmed_at' => null]);
        User::create(['name' => 'No2FA', 'email' => 'no@example.com', 'status' => 'active',
            'two_factor_enabled' => false, 'two_factor_confirmed_at' => null]);

        $result = (new UserSearch)
            ->for(User::query())
            ->fromRequest(Request::create('/', 'GET', ['filter' => ['mfa' => 'active']]))
            ->paginate(10);

        $this->assertCount(1, $result);
        $this->assertEquals('active@example.com', $result->first()->email);
    }

    public function test_filter_mfa_pending(): void
    {
        User::create(['name' => 'Active2FA', 'email' => 'active@example.com', 'status' => 'active',
            'two_factor_enabled' => true, 'two_factor_confirmed_at' => now()]);
        User::create(['name' => 'Pending2FA', 'email' => 'pending@example.com', 'status' => 'active',
            'two_factor_enabled' => true, 'two_factor_confirmed_at' => null]);
        User::create(['name' => 'No2FA', 'email' => 'no@example.com', 'status' => 'active',
            'two_factor_enabled' => false]);

        $result = (new UserSearch)
            ->for(User::query())
            ->fromRequest(Request::create('/', 'GET', ['filter' => ['mfa' => 'pending']]))
            ->paginate(10);

        $this->assertCount(1, $result);
        $this->assertEquals('pending@example.com', $result->first()->email);
    }

    public function test_filter_mfa_off(): void
    {
        User::create(['name' => 'Active2FA', 'email' => 'active@example.com', 'status' => 'active',
            'two_factor_enabled' => true, 'two_factor_confirmed_at' => now()]);
        User::create(['name' => 'No2FA', 'email' => 'no@example.com', 'status' => 'active',
            'two_factor_enabled' => false]);

        $result = (new UserSearch)
            ->for(User::query())
            ->fromRequest(Request::create('/', 'GET', ['filter' => ['mfa' => 'off']]))
            ->paginate(10);

        $this->assertCount(1, $result);
        $this->assertEquals('no@example.com', $result->first()->email);
    }

    public function test_filter_mfa_active_and_pending_union(): void
    {
        User::create(['name' => 'Active2FA', 'email' => 'active@example.com', 'status' => 'active',
            'two_factor_enabled' => true, 'two_factor_confirmed_at' => now()]);
        User::create(['name' => 'Pending2FA', 'email' => 'pending@example.com', 'status' => 'active',
            'two_factor_enabled' => true, 'two_factor_confirmed_at' => null]);
        User::create(['name' => 'No2FA', 'email' => 'no@example.com', 'status' => 'active',
            'two_factor_enabled' => false]);

        $result = (new UserSearch)
            ->for(User::query())
            ->fromRequest(Request::create('/', 'GET', ['filter' => ['mfa' => 'active,pending']]))
            ->paginate(10);

        $this->assertCount(2, $result);
    }

    public function test_no_filters_returns_all(): void
    {
        User::create(['name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'active']);
        User::create(['name' => 'Bob',   'email' => 'bob@example.com',   'status' => 'active']);

        $result = (new UserSearch)
            ->for(User::query())
            ->fromRequest(Request::create('/'))
            ->paginate(10);

        $this->assertCount(2, $result);
    }
}
