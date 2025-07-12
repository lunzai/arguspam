<?php

namespace App\Http\Controllers;

use App\Enums\CacheKey;
use App\Http\Filters\SessionFilter;
use App\Http\Requests\Session\UpdateSessionRequest;
use App\Http\Resources\Session\SessionCollection;
use App\Http\Resources\Session\SessionResource;
use App\Models\Session;
use App\Traits\IncludeRelationships;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SessionController extends Controller
{
    use IncludeRelationships;

    public function index(SessionFilter $filter, Request $request): SessionCollection
    {
        $this->authorize('viewAny', Session::class);
        $pagination = $request->get('per_page', config('pam.pagination.per_page'));
        $sessions = Cache::remember(
            CacheKey::SESSIONS->key($request->get(config('pam.org.request_attribute'))),
            config('cache.default_ttl'),
            function () use ($filter, $pagination) {
                return Session::filter($filter)->paginate($pagination);
            }
        );
        return new SessionCollection($sessions);
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
