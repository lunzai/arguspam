<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Enums\Status;

use function Laravel\Prompts\info;
use function Laravel\Prompts\form;
use function Laravel\Prompts\table;

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
        $response = form()
            ->text(
                name: 'name',
                label: 'Name',
                placeholder: 'John Doe',
                validate: ['name' => 'required|string|max:255|min:2'],
            )
            ->text(
                name: 'email',
                label: 'Email',
                placeholder: 'john@doe.com',
                validate: ['email' => 'required|email'],
            )
            ->password(
                name: 'password',
                label: 'Password',
                placeholder: '********',
                validate: ['password' => 'required|string|min:'.config('pam.password.min_length')],
            )
            ->multiselect(
                name: 'roles',
                label: 'Roles',
                options: $roles->pluck('name', 'id')->toArray(),
                required: true,
                default: [config('auth.default_user_role')],
                validate: ['roles' => 'required|array'],
                scroll: 10,
            )
            ->confirm("Are you sure you want to create user?")
            ->submit();

        $user = new User();
        $user->name = $response['name'];
        $user->email = $response['email'];
        $user->status = Status::ACTIVE;
        $user->password = Hash::make($response['password']);
        $user->save();

        $userRoles = $roles->filter(fn ($role) => in_array($role->name, $response['roles']));
        $user->roles()->attach($userRoles);

        info('User created successfully');
        info('Name: '.$user->name);
        info('Email: '.$user->email);
    }
}
