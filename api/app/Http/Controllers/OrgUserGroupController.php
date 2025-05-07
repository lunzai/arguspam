<?php

namespace App\Http\Controllers;

// use App\Http\Resources\UserGroup\UserGroupCollection;
// use App\Models\Org;
// use App\Traits\IncludeRelationships;
// use Illuminate\Http\Request;
// use Illuminate\Http\Response;

// TODO: REWRITE
// TODO: Doesnt work this way, To remove this file
// class OrgUserGroupController extends Controller
// {
//     use IncludeRelationships;

//     public function index(Org $org): UserGroupCollection
//     {
//         $userGroups = $org->userGroups()->paginate(config('pam.pagination.per_page'));

//         return new UserGroupCollection($userGroups);
//     }

//     public function store(Request $request, Org $org): Response
//     {
//         $validated = $request->validate([
//             'user_group_ids' => ['required', 'array', 'min:1'],
//             'user_group_ids.*' => ['required', 'exists:user_groups,id'],
//         ]);

//         $org->userGroups()->syncWithoutDetaching($validated['user_group_ids']);

//         return $this->created();
//     }

//     public function destroy(Org $org, Request $request): Response
//     {
//         $validated = $request->validate([
//             'user_group_ids' => ['required', 'array', 'min:1'],
//             'user_group_ids.*' => ['integer', 'exists:user_groups,id']
//         ]);

//         $org->userGroups()->detach($validated['user_group_ids']);

//         return $this->noContent();
//     }
// }
