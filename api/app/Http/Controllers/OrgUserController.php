<?php

namespace App\Http\Controllers;

use App\Models\Org;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrgUserController extends Controller
{
    public function store(Request $request, Org $org): Response
    {
        $validated = $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['required', 'exists:users,id'],
        ]);

        $org->users()->attach($validated['user_ids']);

        return $this->created();
    }

    public function destroy(Org $org, User $user): Response
    {
        $org->users()->detach($user);

        return $this->noContent();
    }
}
