<?php

namespace App\Http\Controllers;

use App\Enums\CacheKey;
use App\Http\Filters\RequestFilter;
use App\Http\Requests\Request\StoreRequestRequest;
use App\Http\Requests\Request\UpdateRequestRequest;
use App\Http\Resources\Request\RequestCollection;
use App\Http\Resources\Request\RequestResource;
use App\Models\Request as RequestModel;
use App\Traits\IncludeRelationships;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class RequestController extends Controller
{
    use IncludeRelationships;

    /**
     * Display a listing of the resource.
     */
    public function index(RequestFilter $filter, Request $request): RequestCollection
    {
        $this->authorize('viewAny', RequestModel::class);
        $pagination = $request->get('per_page', config('pam.pagination.per_page'));
        // $requests = Cache::remember(
        //     CacheKey::REQUESTS->key($request->get(config('pam.org.request_attribute'))),
        //     config('cache.default_ttl'),
        //     function () use ($filter, $pagination) {
        //         return RequestModel::filter($filter)->paginate($pagination);
        //     }
        // );
        $requests = RequestModel::filter($filter)
            ->paginate($pagination);
        return new RequestCollection($requests);
    }

    public function store(StoreRequestRequest $request): RequestResource
    {
        $this->authorize('create', RequestModel::class);
        $validated = $request->validated();
        $request = RequestModel::create($validated);

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

    public function update(UpdateRequestRequest $request, string $id): RequestResource
    {
        $request = RequestModel::findOrFail($id);
        $this->authorize('update', $request);
        $validated = $request->validated();
        $request->update($validated);

        return new RequestResource($request);
    }

    public function destroy(string $id): Response
    {
        $request = RequestModel::findOrFail($id);
        $this->authorize('delete', $request);
        $request->deleted_by = Auth::id();
        $request->save();
        $request->delete();

        return $this->noContent();
    }
}
