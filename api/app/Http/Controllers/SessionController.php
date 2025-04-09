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
        $session = Session::filter($filter);

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

    public function update(UpdateSessionRequest $request, Session $session): SessionResource
    {
        $validated = $request->validated();
        $session->update($validated);

        return new SessionResource($session);
    }

    public function destroy(Session $session): Response
    {
        $session->deleted_by = Auth::id();
        $session->save();
        $session->delete();

        return response()->noContent();
    }
}
