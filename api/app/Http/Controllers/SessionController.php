<?php

namespace App\Http\Controllers;

use App\Http\Requests\Session\UpdateSessionRequest;
use App\Http\Resources\Session\SessionCollection;
use App\Http\Resources\Session\SessionResource;
use App\Models\Session;
use App\Traits\IncludeRelationships;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    use IncludeRelationships;

    public function index(): SessionCollection
    {
        $session = Session::query();
        // $this->applyExpands($session);

        return new SessionCollection(
            $session->paginate(config('pam.pagination.per_page'))
        );
    }

    public function show(string $id): SessionResource
    {
        $session = Session::query();
        $this->applyIncludes($session, request());

        return new SessionResource($session->findOrFail($id));
    }

    public function update(UpdateSessionRequest $request, string $id): SessionResource
    {
        $session = Session::findOrFail($id);
        $validated = $request->validated();
        $validated['updated_by'] = Auth::id();
        $session->update($validated);

        return new SessionResource($session);
    }

    public function destroy(string $id): Response
    {
        $session = Session::findOrFail($id);
        $session->deleted_by = Auth::id();
        $session->save();
        $session->delete();

        return response()->noContent();
    }
}
