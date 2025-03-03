<?php

namespace Database\Seeders;

use App\Models\Org;
use Illuminate\Database\Seeder;

class OrgSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Org::factory()->count(3)->create();
    }
}
