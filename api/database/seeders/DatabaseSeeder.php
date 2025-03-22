<?php

namespace Database\Seeders;

use App\Enums\AssetAccessRole;
use App\Models\Asset;
use App\Models\AssetAccount;
use App\Models\Org;
use App\Models\Role;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    private const ORG_COUNT = 3;

    private const USER_COUNT = 20;

    private const USER_GROUP_COUNT = 5;

    private const ASSET_COUNT = 10;

    private const PERMISSION_COUNT = 10;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PermissionSeeder::class);
        $defaultAdminRole = Role::where('name', config('pam.rbac.default_admin_role'))
            ->first();
        $defaultUserRole = Role::where('name', config('pam.rbac.default_user_role'))
            ->first();
        User::factory(2)
            ->sequence(
                ['name' => 'Admin', 'email' => 'admin@admin.com'],
                ['name' => 'Hean Luen', 'email' => 'heanluen@gmail.com'],
            )
            ->hasAttached($defaultAdminRole)
            ->create();

        $orgs = Org::factory(self::ORG_COUNT)
            ->create();

        for ($i = 0; $i < self::USER_COUNT; $i++) {
            User::factory()
                ->hasAttached(
                    $orgs->random(1),
                    ['joined_at' => now()->subDays(rand(0, 3))]
                )
                ->hasAttached($defaultUserRole)
                ->create();
        }

        $users = User::all();
        for ($i = 0; $i < self::USER_GROUP_COUNT; $i++) {
            UserGroup::factory()
                ->recycle($orgs)
                ->hasAttached(
                    $users->random(rand(1, count($users)))
                )
                ->create();
        }

        $assets = [];
        for ($i = 0; $i < self::ASSET_COUNT; $i++) {
            $asset = Asset::factory()
                ->recycle($orgs)
                ->has(
                    AssetAccount::factory()
                        ->count(rand(1, 2)),
                    'accounts'
                )
                ->create();
            $assetOrgUsers = $asset->org->users->shuffle();
            $requesterCount = rand(1, $assetOrgUsers->count());

            // TODO: Fix this
            $asset->users()->attach([
                ...$assetOrgUsers->take($requesterCount)
                    ->map(fn ($user) => [
                        'asset_id' => $asset->id,
                        'user_id' => $user->id,
                        'created_by' => 1,
                        'updated_by' => 1,
                        'role' => AssetAccessRole::REQUESTER->value,
                    ])
                    ->all(),
                ...$assetOrgUsers->skip($requesterCount)
                    ->map(fn ($user) => [
                        'asset_id' => $asset->id,
                        'user_id' => $user->id,
                        'created_by' => 1,
                        'updated_by' => 1,
                        'role' => AssetAccessRole::APPROVER->value,
                    ])
                    ->all(),
            ]);
            $assets[] = $asset;
        }
    }
}
