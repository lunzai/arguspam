<?php

namespace App\Http\Controllers;

use App\Http\Requests\Request\ApproverRequestRequest;
use App\Http\Resources\Request\RequestResource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequestRejectController extends Controller
{
    public function show(Request $request)
    {
        $this->authorize('request:reject');
        if (!Auth::user()->canApprove($request->asset)) {
            throw new AuthorizationException('You are not authorized to reject this request.');
        }
        return $this->noContent();
    }

    public function update(ApproverRequestRequest $request, string $id): RequestResource
    {
        $request = Request::findOrFail($id);
        $this->authorize('reject', $request);
        $validated = $request->validated();
        $request->reject($validated['rejecter_note'], $validated['rejecter_risk_rating']);

        return new RequestResource($request);
    }
}
