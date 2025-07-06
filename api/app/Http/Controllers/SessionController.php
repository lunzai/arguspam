<?php

namespace App\Http\Controllers;

use App\Http\Filters\SessionFilter;
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

    public function index(SessionFilter $filter): SessionCollection
    {
        $this->authorize('viewAny', Session::class);
        $session = Session::filter($filter);

        return new SessionCollection(
            $session->paginate(config('pam.pagination.per_page'))
        );
    }

    public function show(string $id): SessionResource
    {
        $sessionQuery = Session::query();
        $this->applyIncludes($sessionQuery, request());
        $session = $sessionQuery->findOrFail($id);
        $this->authorize('view', $session);

        return new SessionResource($session);
    }

    public function update(UpdateSessionRequest $request, Session $session): SessionResource
    {
        $this->authorize('update', $session);
        $validated = $request->validated();
        $session->update($validated);

        return new SessionResource($session);
    }

    public function destroy(Session $session): Response
    {
        $this->authorize('delete', $session);
        $session->deleted_by = Auth::id();
        $session->save();
        $session->delete();

        return $this->noContent();
    }
}
