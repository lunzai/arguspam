<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Request;
use App\Models\Session;
use App\Models\UserGroup;
use Illuminate\Http\JsonResponse;

class ExampleController extends Controller
{
    /**
     * Example of how the trait automatically scopes queries to current organization
     */
    public function listUserGroups(): JsonResponse
    {
        // This will automatically only return user groups for the current organization
        // thanks to the global scope in the BelongsToOrganization trait
        $userGroups = UserGroup::all();

        return response()->json([
            'message' => 'User groups for current organization',
            'data' => $userGroups,
            'current_org_id' => UserGroup::getCurrentOrganizationId(),
        ]);
    }

    /**
     * Example of creating a new record - org_id is automatically set
     */
    public function createUserGroup(): JsonResponse
    {
        // The org_id will be automatically set from the current organization context
        $userGroup = UserGroup::create([
            'name' => 'New User Group',
            'description' => 'Created via API',
            'status' => 'active',
        ]);

        return response()->json([
            'message' => 'User group created',
            'data' => $userGroup,
            'org_id_was_set' => $userGroup->org_id,
        ]);
    }

    /**
     * Example of creating for a specific organization
     */
    public function createForSpecificOrg(): JsonResponse
    {
        $userGroup = UserGroup::createForOrganization([
            'name' => 'Specific Org Group',
            'description' => 'Created for specific organization',
            'status' => 'active',
        ], 1); // org_id = 1

        return response()->json([
            'message' => 'User group created for specific organization',
            'data' => $userGroup,
        ]);
    }

    /**
     * Example of bypassing organization scope (for admin operations)
     */
    public function listAllUserGroups(): JsonResponse
    {
        // This bypasses the organization scope to get all user groups
        $allUserGroups = UserGroup::withoutOrganizationScope()->get();

        return response()->json([
            'message' => 'All user groups (bypassing org scope)',
            'data' => $allUserGroups,
        ]);
    }

    /**
     * Example of checking organization membership
     */
    public function checkOrganizationMembership(): JsonResponse
    {
        $userGroup = UserGroup::first();

        if (!$userGroup) {
            return response()->json(['message' => 'No user groups found'], 404);
        }

        return response()->json([
            'user_group_id' => $userGroup->id,
            'is_in_current_org' => $userGroup->isInCurrentOrganization(),
            'current_org_id' => UserGroup::getCurrentOrganizationId(),
            'user_group_org_id' => $userGroup->org_id,
        ]);
    }

    /**
     * Example of getting current organization context
     */
    public function getCurrentOrgContext(): JsonResponse
    {
        return response()->json([
            'has_org_context' => UserGroup::hasOrganizationContext(),
            'current_org_id' => UserGroup::getCurrentOrganizationId(),
            'current_org' => UserGroup::getCurrentOrganization(),
        ]);
    }

    /**
     * Example of working with multiple models that use the trait
     */
    public function multiModelExample(): JsonResponse
    {
        // All these queries will be automatically scoped to the current organization
        $userGroups = UserGroup::count();
        $assets = Asset::count();
        $requests = Request::count();
        $sessions = Session::count();

        return response()->json([
            'message' => 'Counts for current organization',
            'data' => [
                'user_groups' => $userGroups,
                'assets' => $assets,
                'requests' => $requests,
                'sessions' => $sessions,
                'current_org_id' => UserGroup::getCurrentOrganizationId(),
            ],
        ]);
    }
}
