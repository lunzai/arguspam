<?php

namespace Database\Seeders;

use App\Models\UserGroup;
use Illuminate\Database\Seeder;

class UserGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserGroup::factory([
            'org_id' => 1,
        ])
            ->count(10)
            ->create();
    }
}
