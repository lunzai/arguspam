<?php

namespace App\Policies;

use App\Models\OrgAiAgent;
use App\Models\User;

class OrgAiAgentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['org:view', 'org:updateany']);
    }

    public function view(User $user, OrgAiAgent $orgAiAgent): bool
    {
        return $user->hasAnyPermission(['org:view', 'org:updateany']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission('org:updateany');
    }

    public function update(User $user, OrgAiAgent $orgAiAgent): bool
    {
        return $user->hasAnyPermission('org:updateany');
    }

    public function delete(User $user, OrgAiAgent $orgAiAgent): bool
    {
        return $user->hasAnyPermission('org:updateany');
    }
}
