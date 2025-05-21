<?php

namespace App\Http\Middleware;

use App\Enums\Status;
use App\Models\UserAccessRestriction;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class EnforceUserAccessRestrictions
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Get all active restrictions for the user from cache or database
        $restrictions = Cache::remember(
            "user_restrictions_{$user->id}",
            now()->addMinutes(15),
            function () use ($user) {
                return UserAccessRestriction::where('user_id', $user->id)
                    ->where('status', Status::ACTIVE)
                    ->get();
            }
        );

        // If no restrictions, allow access
        if ($restrictions->isEmpty()) {
            return $next($request);
        }

        // Check if all restrictions pass (AND logic)
        foreach ($restrictions as $restriction) {
            if (!$restriction->passes($request)) {
                return response()->json([
                    'message' => 'Access denied due to access restrictions',
                    'restriction_type' => $restriction->type->value,
                ], Response::HTTP_FORBIDDEN);
            }
        }

        return $next($request);
    }
}
