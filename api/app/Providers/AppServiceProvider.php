<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\DashboardPolicy;
use App\Policies\OrgUserPolicy;
use App\Policies\PasswordPolicy;
use App\Policies\RolePermissionPolicy;
use App\Policies\UserGroupUserPolicy;
use App\Policies\UserOrgPolicy;
use App\Policies\UserRolePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Telescope\TelescopeServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('dashboard:viewany', [DashboardPolicy::class, 'viewAny']);
        Gate::define('orguser:viewany', [OrgUserPolicy::class, 'viewAny']);
        Gate::define('orguser:create', [OrgUserPolicy::class, 'create']);
        Gate::define('orguser:delete', [OrgUserPolicy::class, 'delete']);
        Gate::define('password:update', [PasswordPolicy::class, 'update']);
        Gate::define('rolepermission:create', [RolePermissionPolicy::class, 'create']);
        Gate::define('rolepermission:delete', [RolePermissionPolicy::class, 'delete']);
        Gate::define('usergroupuser:create', [UserGroupUserPolicy::class, 'create']);
        Gate::define('usergroupuser:delete', [UserGroupUserPolicy::class, 'delete']);
        Gate::define('userorg:viewany', [UserOrgPolicy::class, 'viewAny']);
        Gate::define('userorg:view', [UserOrgPolicy::class, 'view']);
        Gate::define('userrole:create', [UserRolePolicy::class, 'create']);
        Gate::define('userrole:delete', [UserRolePolicy::class, 'delete']);
        Gate::before(function (User $user, string $ability) {
            if ($user->isAdmin()) {
                return true;
            }
        });
    }
}
