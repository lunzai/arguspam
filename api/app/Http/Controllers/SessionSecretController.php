<?php

namespace App\Http\Controllers;

use App\Models\Session;
use Illuminate\Support\Facades\Auth;

class SessionSecretController extends Controller
{
    /**
     * Show sesssion secret
     */
    public function show(Session $session)
    {
        $this->authorize('retrieveSecret', $session);
        $isOwner = $session->requester->is(Auth::user());
        return [
            'data' => [
                'canEnd' => $isOwner && $session->canEnd(),
                'canStart' => $isOwner && $session->canStart(),
                'canCancel' => $isOwner && $session->canCancel(),
                'canTerminate' => $isOwner && $session->canTerminate(),
            ],
        ];
    }
}
