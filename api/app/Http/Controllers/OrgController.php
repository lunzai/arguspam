<?php

namespace App\Http\Controllers;

use App\Http\Requests\Org\StoreOrgRequest;
use App\Http\Requests\Org\UpdateOrgRequest;
use App\Http\Resources\Org\OrgCollection;
use App\Http\Resources\Org\OrgResource;
use App\Models\Org;
use Illuminate\Support\Facades\Auth;

class OrgController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return new OrgCollection(
            Org::paginate(config('constants.pagination.per_page'))
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrgRequest $request)
    {
        $validated = $request->validated();
        $validated['created_by'] = Auth::id();
        $org = Org::create($validated);

        return new OrgResource($org);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return new OrgResource(Org::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrgRequest $request, string $id)
    {
        $org = Org::findOrFail($id);
        $validated = $request->validated();
        $validated['updated_by'] = Auth::id();
        $org->update($validated);

        return new OrgResource($org);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $org = Org::findOrFail($id);
        $org->deleted_by = Auth::id();
        $org->save();
        $org->delete();

        return response()->noContent();
    }
}
