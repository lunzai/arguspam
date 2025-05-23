<?php

namespace App\Http\Controllers;

use App\Http\Filters\RequestFilter;
use App\Http\Requests\Request\StoreRequestRequest;
use App\Http\Requests\Request\UpdateRequestRequest;
use App\Http\Resources\Request\RequestCollection;
use App\Http\Resources\Request\RequestResource;
use App\Models\Request as RequestModel;
use App\Traits\IncludeRelationships;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    use IncludeRelationships;

    /**
     * Display a listing of the resource.
     */
    public function index(RequestFilter $filter): RequestCollection
    {
        $request = RequestModel::filter($filter);

        return new RequestCollection(
            $request->paginate(config('pam.pagination.per_page'))
        );
    }

    public function store(StoreRequestRequest $request): RequestResource
    {
        $validated = $request->validated();
        $request = RequestModel::create($validated);

        return new RequestResource($request);
    }

    public function show(string $id): RequestResource
    {
        $request = RequestModel::query();
        $this->applyIncludes($request, request());

        return new RequestResource($request->findOrFail($id));
    }

    public function update(UpdateRequestRequest $request, string $id): RequestResource
    {
        $request = RequestModel::findOrFail($id);
        $validated = $request->validated();
        $request->update($validated);

        return new RequestResource($request);
    }

    public function destroy(string $id): Response
    {
        $request = RequestModel::findOrFail($id);
        $request->deleted_by = Auth::id();
        $request->save();
        $request->delete();

        return $this->noContent();
    }
}
