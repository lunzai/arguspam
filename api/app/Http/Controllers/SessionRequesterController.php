<?php

namespace App\Http\Controllers;

use App\Http\Resources\Session\SessionResource;
use App\Models\Session;
use Illuminate\Support\Facades\Auth;

class SessionRequesterController extends Controller
{
    /**
     * Check if user can start/end/cancel session
     */
    public function show(Session $session)
    {
        $this->authorize('permission', $session);
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
     * Requester: Start session
     */
    public function store(Session $session): SessionResource
    {
        if (!$session->canStart()) {
            return $this->unprocessableEntity('Session is not eligible for starting');
        }
        $this->authorize('start', $session);
        $session->start();
        return new SessionResource($session);
    }

    /**
     * Requester: End session
     */
    public function update(Session $session): SessionResource
    {
        if (!$session->canEnd()) {
            return $this->unprocessableEntity('Session is not eligible for ending');
        }
        $this->authorize('end', $session);
        $session->end();
        return new SessionResource($session);
    }

    /**
     * Requester: Cancel session
     */
    public function delete(Session $session): SessionResource
    {
        if (!$session->canCancel()) {
            return $this->unprocessableEntity('Session is not eligible for cancellation');
        }
        $this->authorize('cancel', $session);
        $session->cancel();
        return new SessionResource($session);
    }
}
