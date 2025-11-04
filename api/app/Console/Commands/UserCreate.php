<?php

namespace App\Console\Commands;

use App\Enums\Status;
use App\Models\Org;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

use function Laravel\Prompts\form;
use function Laravel\Prompts\info;

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
        $orgs = Org::all();
        $timezoneList = collect(\DateTimeZone::listIdentifiers());

        $response = form()
            ->text(
                label: 'Name', 
                required: true, 
                name: 'name',
                validate: ['string', 'min:2', 'max:100']
            )
            ->text(
                label: 'Email', 
                required: true, 
                name: 'email',
                validate: ['email', 'unique:users,email']
            )
            ->password(
                label: 'Password', 
                required: true, 
                hint: 'Password must be at least 8 characters long', 
                validate: ['string', 'min:8'],
                name: 'password'
            )
            ->search(
                label: 'Default Timezone (autocomplete)', 
                options: fn (string $value) => strlen($value) > 0
                ? $timezoneList->filter(fn ($tz) => str_contains(strtolower($tz), strtolower($value)))
                    ->take(5)->values()->all()
                : [], 
                required: true,
                name: 'default_timezone'
            )
            ->multiselect('Roles (Multiple choice)', $roles->mapWithKeys(fn($item) => [$item['id'] => $item['name']])
                ->all(), 
                required: true,
                name: 'roles'
            )
            ->multiselect('Orgs (Multiple choice)', $orgs->mapWithKeys(fn($item) => [$item['id'] => $item['name']])
                ->all(), 
                required: true,
                name: 'orgs'
            )
            ->confirm(
                label: 'Are you sure you want to create user?', 
                required: true,
                name: 'confirm'
            )
            ->submit();

        $user = new User;
        $user->name = $response['name'];
        $user->email = $response['email'];
        $user->status = Status::ACTIVE;
        $user->default_timezone = $response['default_timezone'];
        $user->password = $response['password'];
        $user->save();

        if (!empty($response['roles'])) {
            $user->roles()->attach($response['roles']);
        }

        if (!empty($response['orgs'])) {
            $user->orgs()->attach($response['orgs']);
        }

        info('User created successfully');
        info('User ID: ' . $user->id);
        info('Name: ' . $user->name);
        info('Email: ' . $user->email);
        info('Default Timezone: ' . $user->default_timezone);
        info('Roles: ' . $user->roles->pluck('name')->implode(', '));
        info('Orgs: ' . $user->orgs->pluck('name')->implode(', '));
    }
}
