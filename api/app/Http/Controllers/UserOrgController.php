<?php

namespace App\Http\Controllers;

use App\Models\Org;
use App\Http\Resources\Org\OrgCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class UserOrgController extends Controller
{
    /**
     * Return all orgs of the auth user
     *
     * @return OrgCollection
     */
    public function index(): OrgCollection
    {
        $orgs = Auth::user()
            ->orgs()
            ->paginate(config('pam.pagination.per_page'));
        return new OrgCollection($orgs);
    }

    /**
     * Check if the auth user is a member of the org
     *
     * @param Org $org
     * @return Response
     */
    public function show(Org $org): Response
    {
        Auth::user()
            ->orgs()
            ->findOrFail($org->id);
        return $this->ok();
    }
}
