<?php

namespace Database\Seeders;

use App\Enums\AssetAccessRole;
use App\Enums\UserRole;
use App\Models\Asset;
use App\Models\AssetAccount;
use App\Models\Org;
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

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(2)
            ->sequence(
                ['name' => 'Admin', 'email' => 'admin@admin.com', 'role' => UserRole::ADMIN],
                ['name' => 'Hean Luen', 'email' => 'heanluen@gmail.com', 'role' => UserRole::ADMIN],
            )
            ->create();

        $orgs = Org::factory(self::ORG_COUNT)
            ->create();

        for ($i = 0; $i < self::USER_COUNT; $i++) {
            User::factory()
                ->hasAttached(
                    $orgs->random(1),
                    ['joined_at' => now()->subDays(rand(0, 3))]
                )
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

            $asset->users()->attach([
                ...$assetOrgUsers->take($requesterCount)
                    ->mapWithKeys(fn ($user) => [$user->id => ['role' => AssetAccessRole::REQUESTER->value]])
                    ->all(),
                ...$assetOrgUsers->skip($requesterCount)
                    ->mapWithKeys(fn ($user) => [$user->id => ['role' => AssetAccessRole::APPROVER->value]])
                    ->all(),
            ]);
            $assets[] = $asset;
        }
    }
}
