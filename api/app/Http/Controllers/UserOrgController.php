<?php

namespace App\Http\Controllers;

use App\Http\Resources\Org\OrgCollection;
use App\Models\Org;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class UserOrgController extends Controller
{
    /**
     * Return all orgs of the auth user
     */
    public function index(): OrgCollection
    {
        $user = Auth::user();
        $this->authorize('view', $user);
        return new OrgCollection($user->orgs()->get());
    }

    /**
     * Check if the auth user is a member of the org
     */
    public function show(Org $org): Response
    {
        $user = Auth::user();
        $this->authorize('view', $user);
        Auth::user()
            ->orgs()
            ->findOrFail($org->id);
        return $this->ok();
    }
}
