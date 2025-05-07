<?php

namespace App\Http\Controllers;

use App\Models\Org;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrgUserController extends Controller
{
    public function store(Request $request, Org $org): Response
    {
        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'exists:users,id'],
        ]);

        $org->users()->syncWithoutDetaching($validated['user_ids']);

        return $this->created();
    }

    public function destroy(Org $org, Request $request): Response
    {
        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $org->users()->detach($validated['user_ids']);

        return $this->noContent();
    }
}
