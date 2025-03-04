<?php

namespace App\Http\Controllers;

use App\Http\Requests\Session\UpdateSessionRequest;
use App\Http\Resources\Session\SessionCollection;
use App\Http\Resources\Session\SessionResource;
use App\Models\Session;
use Illuminate\Support\Facades\Auth;
use App\Traits\IsExpandable;

class SessionController extends Controller
{
    use IsExpandable;

    public function index()
    {
        $session = Session::query();
        $this->applyExpands($session);
        return new SessionCollection(
            $session->paginate(config('constants.pagination.per_page'))
        );
    }

    public function show(string $id)
    {
        $session = Session::query();
        $this->applyExpands($session);
        return new SessionResource($session->findOrFail($id));
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
