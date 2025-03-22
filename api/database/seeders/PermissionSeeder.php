<?php

namespace Database\Seeders;

use App\Services\PolicyPermissionService;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    private PolicyPermissionService $policyPermissionService;

    public function __construct(PolicyPermissionService $policyPermissionService)
    {
        $this->policyPermissionService = $policyPermissionService;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->policyPermissionService->syncPermissions(true);
    }
}
