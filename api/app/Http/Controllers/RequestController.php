<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatus;
use App\Http\Filters\RequestFilter;
use App\Http\Requests\Request\StoreRequestRequest;
use App\Http\Resources\Request\RequestCollection;
use App\Http\Resources\Request\RequestResource;
use App\Models\Request as RequestModel;
use App\Traits\IncludeRelationships;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    use IncludeRelationships;

    public function index(RequestFilter $filter, Request $request): RequestCollection
    {
        $this->authorize('view', RequestModel::class);
        $pagination = $request->get('per_page', config('pam.pagination.per_page'));
        $requests = RequestModel::filter($filter)
            ->paginate($pagination);
        return new RequestCollection($requests);
    }

    public function store(StoreRequestRequest $request): RequestResource
    {
        $this->authorize('create', RequestModel::class);
        $validated = $request->validated();
        $request = new RequestModel($validated);
        $request->status = RequestStatus::PENDING;
        $request->save();

        return new RequestResource($request);
    }

    public function show(string $id): RequestResource
    {
        $requestQuery = RequestModel::query();
        $this->applyIncludes($requestQuery, request());
        $request = $requestQuery->findOrFail($id);
        $this->authorize('view', $request);

        return new RequestResource($request);
    }
}
