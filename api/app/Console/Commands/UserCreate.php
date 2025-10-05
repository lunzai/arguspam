<?php

namespace App\Console\Commands;

use App\Enums\Status;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class UserCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $roles = Role::all();
        $roleOptions = $roles->pluck('name', 'id')->toArray();
        $defaultRole = config('pam.rbac.default_user_role');

        $searchResult = array_search($defaultRole, $roleOptions);
        $defaultRoleIndex = $searchResult === false ? null : $searchResult;

        $name = $this->ask('Name');
        $email = $this->ask('Email');
        $password = $this->secret('Password');
        $defaultTimezone = $this->anticipate('Default Timezone', \DateTimeZone::listIdentifiers());

        $selectedRoles = [];
        if (!empty($roleOptions)) {
            $selectedRoles = $this->choice('Roles (Multiple choice)', $roleOptions, $defaultRoleIndex, null, true);
        } else {
            $this->warn('No roles available. User will be created without roles.');
        }

        if (!$this->confirm('Are you sure you want to create user?')) {
            return;
        }

        $user = new User;
        $user->name = $name;
        $user->email = $email;
        $user->status = Status::ACTIVE;
        $user->default_timezone = $defaultTimezone;
        $user->password = $password;
        $user->save();

        if (!empty($selectedRoles)) {
            $userRoles = $roles->filter(fn ($role) => in_array($role->name, $selectedRoles));
            $user->roles()->attach($userRoles);
        }

        $this->info('User created successfully');
        $this->info('Name: '.$user->name);
        $this->info('Email: '.$user->email);
        $this->info('Default Timezone: '.$user->default_timezone);
    }
}
