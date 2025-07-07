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
        $defaultRole = config('auth.default_user_role');

        $name = $this->ask('Name');
        $email = $this->ask('Email');
        $password = $this->secret('Password');

        $selectedRoles = [];
        if (!empty($roleOptions)) {
            $selectedRoles = $this->choice('Roles', $roleOptions, $defaultRole, null, true);
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
        $user->password = $password;
        $user->save();

        if (!empty($selectedRoles)) {
            $userRoles = $roles->filter(fn ($role) => in_array($role->name, $selectedRoles));
            $user->roles()->attach($userRoles);
        }

        $this->info('User created successfully');
        $this->info('Name: '.$user->name);
        $this->info('Email: '.$user->email);
    }
}
