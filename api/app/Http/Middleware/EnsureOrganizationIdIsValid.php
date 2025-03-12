<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizationIdIsValid
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $orgId = $request->header(config('pam.org.request_header'));

        if (!$orgId) {
            return response()->json([
                'message' => 'Organization ID is required',
                'errors' => [
                    'organization' => ['Organization ID header is missing'],
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        // Check if user has access to this organization
        $hasAccess = $request->user()
            ->orgs()
            ->where('id', $orgId)
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'message' => 'Unauthorized access to organization',
                'errors' => [
                    'organization' => ['You do not have access to this organization'],
                ],
            ], Response::HTTP_FORBIDDEN);
        }
        // Store org_id in request for later use
        $request->merge([config('pam.org.request_attribute') => $orgId]);

        return $next($request);
    }
}
