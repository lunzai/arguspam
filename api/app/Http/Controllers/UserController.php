<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserCollection;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    use ApiResponses;

    public function index()
    {
        return new UserCollection(
            User::paginate(config('constants.pagination.per_page'))
        );
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();
        $validated['created_by'] = Auth::id();
        $user = User::create($validated);

        return new UserResource($user);
    }

    public function show(string $id)
    {
        // return $this->success(User::find($id));
        return new UserResource(User::findOrFail($id));
    }

    public function update(UpdateUserRequest $request, string $id)
    {
        $user = User::findOrFail($id);
        $validated = $request->validated();
        $validated['updated_by'] = Auth::id();
        $user->update($validated);

        return new UserResource($user);
    }

    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->deleted_by = Auth::id();
        $user->save();
        $user->delete();

        return response()->noContent();
    }
}
