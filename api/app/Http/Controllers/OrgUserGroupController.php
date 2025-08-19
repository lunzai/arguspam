<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\UserGroup\UserGroupCollection;
use App\Models\Org;

class OrgUserGroupController extends Controller
{
    public function index(Org $org, Request $request): UserGroupCollection
    {
        $this->authorize('orgusergroup:viewany', $org);
        $pagination = $request->get('per_page', config('pam.pagination.per_page'));
        $userGroups = $org->userGroups()
            ->paginate($pagination);
        return new UserGroupCollection($userGroups);
    }
}
