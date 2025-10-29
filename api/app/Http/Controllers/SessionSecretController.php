<?php

namespace App\Http\Controllers;

use App\Http\Resources\Session\SessionSecretResource;
use App\Models\Session;

class SessionSecretController extends Controller
{
    /**
     * Show sesssion secret
     */
    public function show(Session $session): SessionSecretResource
    {
        if (!$session->canRetrieveSecret()) {
            return $this->unprocessableEntity('Session is not eligible for secret retrieval');
        }
        $this->authorize('retrieveSecret', $session);
        return new SessionSecretResource($session->load('asset', 'assetAccount'));
    }
}
