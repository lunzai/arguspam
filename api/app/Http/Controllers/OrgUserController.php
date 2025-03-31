<?php

namespace App\Http\Controllers;

use App\Models\Org;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;

class OrgUserController extends Controller
{
    public function store(Request $request, Org $org): Response
    {
        $validated = $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['required', 'exists:users,id'],
        ]);

        $org->users()->attach($validated['user_ids']);

        return response()->noContent(Response::HTTP_CREATED);
    }

    public function destroy(Org $org, User $user): Response
    {
        $org->users()->detach($user);

        return response()->noContent();
    }
}
