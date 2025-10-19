<?php

namespace App\Http\Controllers;

use App\Http\Resources\Session\SessionResource;
use App\Models\Session;

class SessionApproverController extends Controller
{
    /**
     * Terminate session
     */
    public function delete(Session $session): SessionResource
    {
        $this->authorize('terminate', $session);
        
        if ($session->canTerminate()) {
            $session->terminate();
        }

        return new SessionResource($session);
    }
}