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
        $this->authorize('userorg:viewany');
        $orgs = Auth::user()
            ->orgs()
            ->paginate(config('pam.pagination.per_page'));
        return new OrgCollection($orgs);
    }

    /**
     * Check if the auth user is a member of the org
     */
    public function show(Org $org): Response
    {
        $this->authorize('userorg:view', $org);
        Auth::user()
            ->orgs()
            ->findOrFail($org->id);
        return $this->ok();
    }
}
