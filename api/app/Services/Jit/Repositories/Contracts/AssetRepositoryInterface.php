<?php

namespace App\Services\Jit\Repositories\Contracts;

use App\Models\Asset;
use App\Models\AssetAccount;

interface AssetRepositoryInterface
{
    public function getActiveAdminAccount(Asset $asset): ?AssetAccount;
}
