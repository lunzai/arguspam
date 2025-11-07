<?php

namespace App\Console\Commands;

use App\Enums\Status;
use App\Models\Org;
use App\Models\Permission;
use App\Models\Role;
use App\Services\PolicyPermissionService;
use Illuminate\Console\Command;

use function Laravel\Prompts\error;
use function Laravel\Prompts\note;
use function Laravel\Prompts\progress;

class PamInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pam:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install ArgusPAM';

    public function __construct(private PolicyPermissionService $policyPermissionService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (Role::count() > 0) {
            error('It looks like ArgusPAM is already installed.');
            note('If you want to create a new user, you can run the following command:');
            note('php artisan user:create');
            return;
        }

        $progress = progress(
            label: 'Setting up ArgusPAM...',
            steps: 10,
        );
        $progress->start();

        $progress->label('Running database migrations...');
        $this->call('migrate', ['--force' => true]);
        $progress->advance();

        $progress->label('Seeding permissions...');
        $this->policyPermissionService->syncPermissions(true);
        $progress->advance();

        $progress->label('Creating default roles...');
        $existingRoles = Role::all();
        if (!$existingRoles->firstWhere('name', config('pam.rbac.default_admin_role'))) {
            $adminRole = new Role([
                'name' => config('pam.rbac.default_admin_role'),
                'description' => 'Default admin role',
            ]);
            $adminRole->is_default = true;
            $adminRole->save();
            $progress->advance();
        }

        if (!$existingRoles->firstWhere('name', config('pam.rbac.default_user_role'))) {
            $userRole = new Role([
                'name' => config('pam.rbac.default_user_role'),
                'description' => 'Default user role',
            ]);
            $userRole->is_default = true;
            $userRole->save();
            $userDefaultPermissions = config('pam.rbac.default_user_permissions');
            $permissions = Permission::whereIn('name', $userDefaultPermissions)->get();
            $userRole->permissions()->sync($permissions);
        }
        $progress->advance();

        $progress->label('Creating default organization...');
        if (Org::count() === 0) {
            Org::create([
                'name' => 'Default Org',
                'status' => Status::ACTIVE,
                'description' => 'Default org',
            ]);
        }
        $progress->advance();

        $progress->label('Optimizing application for performance...');
        $this->call('config:cache');
        $progress->advance();
        $this->call('route:cache');
        $progress->advance();
        $this->call('view:cache');
        $progress->advance();
        $this->call('event:cache');
        $progress->advance();
        $this->call('optimize');

        $progress->finish();

        info('Creating default user...');

        $this->call(UserCreate::class);

        $this->line('');
        info('✅ ArgusPAM installation completed successfully!');
        note('You can now access your application and log in with the credentials you created.');
    }
}
