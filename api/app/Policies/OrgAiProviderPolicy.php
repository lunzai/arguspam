<?php

namespace App\Policies;

use App\Models\OrgAiProvider;
use App\Models\User;

class OrgAiProviderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['org:view', 'org:updateany']);
    }

    public function view(User $user, OrgAiProvider $orgAiProvider): bool
    {
        return $user->hasAnyPermission(['org:view', 'org:updateany']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission('org:updateany');
    }

    public function update(User $user, OrgAiProvider $orgAiProvider): bool
    {
        return $user->hasAnyPermission('org:updateany');
    }

    public function delete(User $user, OrgAiProvider $orgAiProvider): bool
    {
        return $user->hasAnyPermission('org:updateany');
    }
}
