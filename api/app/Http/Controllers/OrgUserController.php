<?php

namespace App\Http\Controllers;

use App\Http\Resources\User\UserCollection;
use App\Models\Org;
use App\Traits\IncludeRelationships;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrgUserController extends Controller
{
    use IncludeRelationships;

    public function index(Org $org): UserCollection
    {
        $users = $org->users()->paginate(config('constants.pagination.per_page'));

        return new UserCollection($users);
    }

    public function store(Request $request, Org $org): Response
    {
        $validated = $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['required', 'exists:users,id'],
        ]);

        $org->users()->attach($validated['user_ids']);

        return response()->noContent(Response::HTTP_CREATED);
    }

    public function destroy(Org $org, string $userId): Response
    {
        $org->users()->detach($userId);

        return response()->noContent();
    }
}
