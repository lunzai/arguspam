<?php

namespace App\Services\Jit\Repositories\Contracts;

use App\Models\Asset;
use App\Models\AssetAccount;

interface AssetRepositoryInterface
{
    public function find(int $id): ?Asset;
    public function getAdminAccount(Asset $asset): ?AssetAccount;
    public function getActiveAdminAccount(Asset $asset): ?AssetAccount;
    public function getJitAccounts(Asset $asset): array;
    public function getExpiredJitAccounts(): array;
}
