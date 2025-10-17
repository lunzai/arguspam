<?php

namespace App\Http\Controllers;

use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Traits\IncludeRelationships;

class UserContextController extends Controller
{
    use IncludeRelationships;

    public function show(User $user): UserResource
    {
        $user->with(
            'orgs',
            'userGroups',
            'requests',
            'sessions',
            'accessRestrictions',
            'roles',
            'permissions'
        );
        return new UserResource($user);
    }
}
