<?php

namespace App\Http\Controllers;

use App\Http\Resources\Session\SessionCollection;
use App\Http\Resources\Session\SessionResource;
use App\Models\Session;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Session\UpdateSessionRequest;

class SessionController extends Controller
{
    public function index()
    {
        return new SessionCollection(
            Session::paginate(config('constants.pagination.per_page'))
        );
    }

    public function show(string $id)
    {
        return new SessionResource(Session::findOrFail($id));
    }

    public function update(UpdateSessionRequest $request, string $id)
    {
        $session = Session::findOrFail($id);
        $validated = $request->validated();
        $validated['updated_by'] = Auth::id();
        $session->update($validated);

        return new SessionResource($session);
    }

    public function destroy(string $id)
    {
        $session = Session::findOrFail($id);
        $session->deleted_by = Auth::id();
        $session->save();
        $session->delete();

        return response()->noContent();
    }
}
