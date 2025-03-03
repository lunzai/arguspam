<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\SessionAudit\SessionAuditCollection;
use App\Http\Resources\SessionAudit\SessionAuditResource;
use App\Models\SessionAudit;

class SessionAuditController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return new SessionAuditCollection(
            SessionAudit::paginate(config('constants.pagination.per_page'))
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return new SessionAuditResource(SessionAudit::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
