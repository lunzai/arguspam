<?php

namespace App\Http\Controllers;

use App\Http\Requests\Request\ApproverRequestRequest;
use App\Http\Resources\Request\RequestResource;
use App\Models\Request as RequestModel;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequestApproverController extends Controller
{
    /**
     * Check if user can approve request
     *
     * @param  Request  $request
     * @return void
     */
    public function show(RequestModel $request)
    {
        if (!$request->canApprove()) {
            throw new AuthorizationException('Request is not eligible for approval.');
        }
        if (!Auth::user()->canApprove($request->asset)) {
            throw new AuthorizationException('You are not authorized to approve this request.');
        }
        return $this->noContent();
    }

    /**
     * Approve request
     */
    public function update(ApproverRequestRequest $request, RequestModel $requestModel): RequestResource
    {
        $this->authorize('request:approve', $requestModel);
        $validated = $request->validated();
        $requestModel->update($validated);
        $requestModel->approve();
        $requestModel->save();

        return new RequestResource($requestModel);
    }

    /**
     * Reject request
     */
    public function destroy(ApproverRequestRequest $request, RequestModel $requestModel): RequestResource
    {
        $this->authorize('request:reject', $requestModel);
        $validated = $request->validated();
        $requestModel->update($validated);
        $requestModel->reject();
        $requestModel->save();

        return new RequestResource($requestModel);
    }
}
