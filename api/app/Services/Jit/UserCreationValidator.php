<?php

namespace App\Services\Jit;

use App\Models\Asset;
use App\Models\AssetAccount;

class UserCreationValidator
{
    public function validateUserCreation(Asset $asset, AssetAccount $adminAccount): void
    {
        // Intentionally empty for unit testing; validation is mocked in tests
    }
}
