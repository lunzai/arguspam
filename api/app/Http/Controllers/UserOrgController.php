<?php

namespace App\Http\Controllers;

use App\Enums\CacheKey;
use App\Http\Resources\Org\OrgCollection;
use App\Models\Org;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class UserOrgController extends Controller
{
    /**
     * Return all orgs of the auth user
     */
    public function index(): OrgCollection
    {
        $this->authorize('userorg:viewany');
        // $orgs = Cache::remember(
        //     CacheKey::USER_ORG->key(Auth::user()->id),
        //     config('cache.default_ttl'),
        //     function () {
        //         return Auth::user()
        //             ->orgs()
        //             ->get();
        //     },
        // );
        $orgs = Auth::user()->orgs()->get();
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
