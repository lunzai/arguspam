<?php

namespace App\Http\Controllers;

use App\Http\Filters\SessionFilter;
use App\Http\Resources\Session\SessionCollection;
use App\Http\Resources\Session\SessionResource;
use App\Models\Session;
use App\Services\Jit\Secrets\SecretsManager;
use App\Traits\IncludeRelationships;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    use IncludeRelationships;
    protected SecretsManager $secretsManager;

    public function __construct(SecretsManager $secretsManager)
    {
        $this->secretsManager = $secretsManager;
    }

    public function index(SessionFilter $filter, Request $request): SessionCollection
    {
        $this->authorize('view', Session::class);
        $pagination = $request->get('per_page', config('pam.pagination.per_page'));
        $sessions = Session::filter($filter)
            ->paginate($pagination);
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

}
