<?php

namespace App\Http\Controllers;

use App\Http\Requests\Request\ApproveRequestRequest;
use App\Http\Requests\Request\RejectRequestRequest;
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
    public function show(RequestModel $requestModel)
    {
        $canApproveOrCancel = $requestModel->canApprove();
        return [
            'data' => [
                'canApprove' => $canApproveOrCancel && Auth::user()->canApprove($requestModel->asset),
                'canCancel' => $canApproveOrCancel && Auth::user()->can('cancel', $requestModel),
            ]
        ];
    }

    /**
     * Approve request
     */
    public function store(ApproveRequestRequest $request, RequestModel $requestModel): RequestResource
    {
        $this->authorize('approve', $requestModel);
        $validated = $request->validated();
        $requestModel->resetApproval();
        $requestModel->update($validated);
        $requestModel->approve();

        return new RequestResource($requestModel);
    }

    /**
     * Reject request
     */
    public function update(RejectRequestRequest $request, RequestModel $requestModel): RequestResource
    {
        $this->authorize('reject', $requestModel);
        $validated = $request->validated();
        $requestModel->resetApproval();
        $requestModel->update($validated);
        $requestModel->reject();

        return new RequestResource($requestModel);
    }

    public function delete(RequestModel $requestModel): RequestResource
    {
        $this->authorize('cancel', $requestModel);
        $requestModel->resetApproval();
        $requestModel->cancel();

        return new RequestResource($requestModel);
    }
}
