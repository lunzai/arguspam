<?php

namespace App\Http\Controllers;

use App\Http\Resources\Session\SessionResource;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionRequesterController extends Controller
{
    /**
     * Check if user can start/end/cancel session
     *
     * @param  Request  $request
     * @return void
     */
    public function show(Session $session)
    {
        $this->authorize('view', $session);
        $isOwner = $session->requester->is(Auth::user());
        return [
            'data' => [
                'canEnd' => $isOwner && $session->canEnd(),
                'canStart' => $isOwner && $session->canStart(),
                'canCancel' => $isOwner && $session->canCancel(),
                'canTerminate' => Auth::user()->can('terminate', $session) && $session->canTerminate(),
                'canRetrieveSecret' => Auth::user()->can('retrieveSecret', $session) && $session->canRetrieveSecret(),
            ],
        ];
    }

    /**
     * Start session
     */
    public function store(Session $session): SessionResource
    {
        $this->authorize('start', $session);
        $session->start();

        return new SessionResource($session);
    }

    /**
     * End session
     */
    public function update(Session $session): SessionResource
    {
        $this->authorize('end', $session);
        $session->end();

        return new SessionResource($session);
    }

    /**
     * Cancel session
     */
    public function delete(Session $session): SessionResource
    {
        $this->authorize('cancel', $session);
        $session->cancel();

        return new SessionResource($session);
    }
}
