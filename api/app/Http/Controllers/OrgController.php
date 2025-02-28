<?php

namespace App\Http\Controllers\Api;

use App\Models\Org;
use App\Http\Requests\StoreOrgRequest;
use App\Http\Requests\UpdateOrgRequest;
use App\Http\Resources\OrgCollection;
use App\Http\Resources\OrgResource;
use Illuminate\Support\Facades\Auth;

class OrgController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return new OrgCollection(Org::paginate(10));
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
        $org = Org::findOrFail($id);
        return new OrgResource($org);
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
