<?php

namespace App\Http\Middleware;

use App\Models\Asset;
use App\Models\AssetAccessGrant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ValidateAssetAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $role = null): Response
    {
        $user = Auth::user();
        $asset = $request->route('asset');

        if (!$asset instanceof Asset) {
            return response()->json([
                'error' => 'Invalid asset',
            ], 400);
        }

        $query = AssetAccessGrant::where('asset_id', $asset->id)
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('userGroup', function ($q) use ($user) {
                        $q->whereHas('users', function ($q) use ($user) {
                            $q->where('user_id', $user->id);
                        });
                    });
            });

        if ($role) {
            $query->where('role', $role);
        }

        if (!$query->exists()) {
            return response()->json([
                'error' => 'You do not have the required access to this asset',
            ], 403);
        }

        return $next($request);
    }
}
