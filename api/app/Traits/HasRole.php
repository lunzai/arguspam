<?php

namespace App\Traits;

use App\Enums\UserRole;
use App\Models\Asset;

trait HasRole
{
    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    public function isAuditor(): bool
    {
        return $this->role === UserRole::AUDITOR;
    }

    public function canRequest(Asset $asset): bool
    {
        return $this->can('request', $asset);
    }

    public function canApprove(Asset $asset): bool
    {
        return $this->can('request', $asset);
    }
}
