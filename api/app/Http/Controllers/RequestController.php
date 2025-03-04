<?php

namespace App\Http\Controllers;

use App\Http\Requests\Request\StoreRequestRequest;
use App\Http\Requests\Request\UpdateRequestRequest;
use App\Http\Resources\Request\RequestCollection;
use App\Http\Resources\Request\RequestResource;
use App\Models\Request as RequestModel;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return new RequestCollection(
            RequestModel::paginate(config('constants.pagination.per_page'))
        );
    }

    public function store(StoreRequestRequest $request)
    {
        $validated = $request->validated();
        $validated['created_by'] = Auth::id();
        $request = RequestModel::create($validated);

        return new RequestResource($request);
    }

    public function show(string $id)
    {
        return new RequestResource(RequestModel::findOrFail($id));
    }

    public function update(UpdateRequestRequest $request, string $id)
    {
        $request = RequestModel::findOrFail($id);
        $validated = $request->validated();
        $validated['updated_by'] = Auth::id();
        $request->update($validated);

        return new RequestResource($request);
    }

    public function destroy(string $id)
    {
        $request = RequestModel::findOrFail($id);
        $request->deleted_by = Auth::id();
        $request->save();
        $request->delete();

        return response()->noContent();
    }
}
