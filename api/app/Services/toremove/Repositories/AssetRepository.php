<?php

namespace App\Services\Jit\Repositories;

use App\Enums\AssetAccountType;
use App\Models\Asset;
use App\Models\AssetAccount;
use App\Services\Jit\Repositories\Contracts\AssetRepositoryInterface;

class AssetRepository implements AssetRepositoryInterface
{
    public function find(int $id): ?Asset
    {
        return Asset::find($id);
    }

    public function getAdminAccount(Asset $asset): ?AssetAccount
    {
        return $asset->accounts()
            ->admin()
            ->first();
    }

    public function getActiveAdminAccount(Asset $asset): ?AssetAccount
    {
        return $asset->accounts()
            ->admin()
            ->active()
            ->first();
    }

    public function getJitAccounts(Asset $asset): array
    {
        return $asset->accounts()
            ->where('type', AssetAccountType::JIT)
            ->get()
            ->toArray();
    }

    public function getExpiredJitAccounts(): array
    {
        return AssetAccount::where('type', AssetAccountType::JIT)
            ->where('expires_at', '<', now())
            ->where('is_active', true)
            ->with('asset')
            ->get()
            ->toArray();
    }
}
