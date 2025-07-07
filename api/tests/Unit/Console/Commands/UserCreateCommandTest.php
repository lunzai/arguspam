<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\UserCreate;
use App\Enums\Status;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserCreateCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('pam.password.min_length', 8);
        Config::set('auth.default_user_role', 1);
        Config::set('hashing.driver', 'bcrypt');

        // Mock Hash facade to avoid HashManager configuration issues
        Hash::shouldReceive('make')
            ->andReturnUsing(function ($password) {
                return 'hashed_'.$password;
            });
        Hash::shouldReceive('isHashed')
            ->andReturn(false);
    }

    public function test_handle_creates_user_successfully()
    {
        Role::query()->delete();
        $adminRole = Role::factory()->create(['name' => 'admin_test1']);
        $userRole = Role::factory()->create(['name' => 'user_test1']);

        $this->artisan(UserCreate::class)
            ->expectsQuestion('Name', 'John Doe')
            ->expectsQuestion('Email', 'john@example.com')
            ->expectsQuestion('Password', 'password123')
            ->expectsChoice('Roles', ['admin_test1'], [$adminRole->id => 'admin_test1', $userRole->id => 'user_test1'])
            ->expectsConfirmation('Are you sure you want to create user?', 'yes')
            ->expectsOutput('User created successfully')
            ->expectsOutput('Name: John Doe')
            ->expectsOutput('Email: john@example.com')
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'status' => Status::ACTIVE,
            'password' => 'hashed_password123',
        ]);

        $user = User::where('email', 'john@example.com')->first();
        $this->assertTrue($user->roles()->where('role_id', $adminRole->id)->exists());
    }

    public function test_handle_creates_user_with_multiple_roles()
    {
        Role::query()->delete();
        $adminRole = Role::factory()->create(['name' => 'admin_test2']);
        $userRole = Role::factory()->create(['name' => 'user_test2']);
        $auditorRole = Role::factory()->create(['name' => 'auditor_test2']);

        $this->artisan(UserCreate::class)
            ->expectsQuestion('Name', 'Jane Doe')
            ->expectsQuestion('Email', 'jane@example.com')
            ->expectsQuestion('Password', 'password123')
            ->expectsChoice('Roles', ['admin_test2', 'auditor_test2'], [$adminRole->id => 'admin_test2', $userRole->id => 'user_test2', $auditorRole->id => 'auditor_test2'])
            ->expectsConfirmation('Are you sure you want to create user?', 'yes')
            ->expectsOutput('User created successfully')
            ->assertExitCode(0);

        $user = User::where('email', 'jane@example.com')->first();
        $this->assertTrue($user->roles()->where('role_id', $adminRole->id)->exists());
        $this->assertTrue($user->roles()->where('role_id', $auditorRole->id)->exists());
        $this->assertFalse($user->roles()->where('role_id', $userRole->id)->exists());
    }

    public function test_handle_uses_default_role_configuration()
    {
        Role::query()->delete();
        $adminRole = Role::factory()->create(['name' => 'admin_test3']);
        $userRole = Role::factory()->create(['name' => 'user_test3']);

        Config::set('auth.default_user_role', $userRole->id);

        $this->artisan(UserCreate::class)
            ->expectsQuestion('Name', 'Test User')
            ->expectsQuestion('Email', 'test@example.com')
            ->expectsQuestion('Password', 'password123')
            ->expectsChoice('Roles', ['user_test3'], [$adminRole->id => 'admin_test3', $userRole->id => 'user_test3'])
            ->expectsConfirmation('Are you sure you want to create user?', 'yes')
            ->assertExitCode(0);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue($user->roles()->where('role_id', $userRole->id)->exists());
    }

    public function test_command_signature()
    {
        $command = new UserCreate;
        $this->assertEquals('user:create', $command->getName());
    }

    public function test_command_description()
    {
        $command = new UserCreate;
        $this->assertEquals('Create a new user', $command->getDescription());
    }

    public function test_user_status_is_set_to_active()
    {
        Role::query()->delete();
        $role = Role::factory()->create(['name' => 'user_test4']);

        $this->artisan(UserCreate::class)
            ->expectsQuestion('Name', 'Active User')
            ->expectsQuestion('Email', 'active@example.com')
            ->expectsQuestion('Password', 'password123')
            ->expectsChoice('Roles', ['user_test4'], [$role->id => 'user_test4'])
            ->expectsConfirmation('Are you sure you want to create user?', 'yes')
            ->assertExitCode(0);

        $user = User::where('email', 'active@example.com')->first();
        $this->assertEquals(Status::ACTIVE, $user->status);
    }

    public function test_password_is_hashed()
    {
        Role::query()->delete();
        $role = Role::factory()->create(['name' => 'user_test5']);

        Hash::partialMock();

        $this->artisan(UserCreate::class)
            ->expectsQuestion('Name', 'Hash Test')
            ->expectsQuestion('Email', 'hash@example.com')
            ->expectsQuestion('Password', 'plaintext_password')
            ->expectsChoice('Roles', ['user_test5'], [$role->id => 'user_test5'])
            ->expectsConfirmation('Are you sure you want to create user?', 'yes')
            ->assertExitCode(0);

        $user = User::where('email', 'hash@example.com')->first();
        $this->assertEquals('hashed_plaintext_password', $user->password);
    }

    public function test_handle_exits_when_user_cancels()
    {
        Role::query()->delete();
        $role = Role::factory()->create(['name' => 'user_test6']);

        $this->artisan(UserCreate::class)
            ->expectsQuestion('Name', 'Cancelled User')
            ->expectsQuestion('Email', 'cancelled@example.com')
            ->expectsQuestion('Password', 'password123')
            ->expectsChoice('Roles', ['user_test6'], [$role->id => 'user_test6'])
            ->expectsConfirmation('Are you sure you want to create user?', 'no')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('users', [
            'email' => 'cancelled@example.com',
        ]);
    }

    public function test_handle_works_when_no_roles_exist()
    {
        Role::query()->delete();

        $this->artisan(UserCreate::class)
            ->expectsQuestion('Name', 'No Roles User')
            ->expectsQuestion('Email', 'noroles@example.com')
            ->expectsQuestion('Password', 'password123')
            ->expectsOutput('No roles available. User will be created without roles.')
            ->expectsConfirmation('Are you sure you want to create user?', 'yes')
            ->expectsOutput('User created successfully')
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'name' => 'No Roles User',
            'email' => 'noroles@example.com',
        ]);

        $user = User::where('email', 'noroles@example.com')->first();
        $this->assertEquals(0, $user->roles()->count());
    }

    public function test_handle_fails_with_duplicate_email()
    {
        Role::query()->delete();
        $role = Role::factory()->create(['name' => 'user_test7']);

        User::factory()->create(['email' => 'duplicate@example.com']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        $this->artisan(UserCreate::class)
            ->expectsQuestion('Name', 'Duplicate User')
            ->expectsQuestion('Email', 'duplicate@example.com')
            ->expectsQuestion('Password', 'password123')
            ->expectsChoice('Roles', ['user_test7'], [$role->id => 'user_test7'])
            ->expectsConfirmation('Are you sure you want to create user?', 'yes');
    }
}
