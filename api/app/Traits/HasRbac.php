<?php

namespace App\Traits;

use App\Enums\CacheKey;
use App\Models\Asset;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;

trait HasRbac
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function getAllRoles(): Collection
    {
        return Cache::remember(
            CacheKey::USER_ROLES->key($this->id),
            config('cache.default_ttl'),
            fn () => $this->roles
        );
    }

    public function isAdmin(): bool
    {
        return $this->roles->contains(config('pam.rbac.default_admin_role'));
    }

    public function getAllPermissions(): Collection
    {
        $roleIds = $this->getAllRoles()
            ->pluck('id');

        return Cache::remember(
            CacheKey::USER_PERMISSIONS->key($this->id),
            config('cache.default_ttl'),
            function () use ($roleIds) {
                return Permission::whereRelation('roles', 'roles.id', $roleIds)
                    ->get();
            }
        );
    }

    public function hasAnyPermission(array|string $permissions): bool
    {
        $permissions = collect(is_array($permissions) ? $permissions : [$permissions])
            ->map(fn ($p) => strtolower($p));

        return $this->getAllPermissions()
            ->pluck('name')
            ->map(fn ($name) => strtolower($name))
            ->intersect($permissions)
            ->isNotEmpty();
    }

    public function clearUserRolePermissionCache($userId = null): void
    {
        $userId = $userId ?? $this->id;
        Cache::forget(CacheKey::USER_ROLES->key($userId));
        Cache::forget(CacheKey::USER_PERMISSIONS->key($userId));
    }

    public function canRequestAsset(User $user, Asset $asset): bool
    {
        return $user->allRequesterAssets()
            ->get()
            ->contains($asset);
    }

    public function canApproveAsset(User $user, Asset $asset): bool
    {
        return $user->allApproverAssets()
            ->get()
            ->contains($asset);
    }

    public function canAccessAsset(User $user, Asset $asset): bool
    {
        return $user->allAssets()
            ->get()
            ->contains($asset);
    }
}
