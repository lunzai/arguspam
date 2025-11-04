<?php

namespace Database\Seeders;

use App\Enums\AssetAccessRole;
use App\Enums\Status;
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

    private const ORG_COUNT = 2;

    private const USER_COUNT = 50;

    private const USER_GROUP_COUNT = 5;

    private const ASSET_COUNT = 8;

    private const REQUEST_COUNT = 5;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // $this->call(PermissionSeeder::class);
        $defaultAdminRole = Role::where('name', config('pam.rbac.default_admin_role'))
            ->first();
        $defaultUserRole = Role::where('name', config('pam.rbac.default_user_role'))
            ->first();

        $defaultUser = User::factory(1)
            ->sequence(
                // ['name' => 'Admin', 'email' => 'admin@admin.com', 'default_timezone' => 'Asia/Singapore'],
                ['name' => 'Hean Luen', 'email' => 'heanluen@gmail.com', 'default_timezone' => 'Asia/Singapore'],
            )
            ->hasAttached($defaultAdminRole)
            ->create();

        $orgs = Org::factory()
            ->count(self::ORG_COUNT)
            ->sequence(
                ['name' => 'Sayyam Investments', 'status' => Status::ACTIVE],
                ['name' => 'Bullsmart', 'status' => Status::ACTIVE],
                ['name' => 'SMB', 'status' => Status::ACTIVE],
                ['name' => 'KKBH', 'status' => Status::ACTIVE],
                ['name' => 'SMDT', 'status' => Status::ACTIVE],
                ['name' => 'Surfin Creatives', 'status' => Status::ACTIVE],
                ['name' => 'Surfin', 'status' => Status::ACTIVE],
                ['name' => 'Inovest', 'status' => Status::ACTIVE],
                ['name' => 'PinjamYuk', 'status' => Status::ACTIVE],
            )
            ->create();

        $defaultUser->each(function ($user) use ($orgs) {
            $user->orgs()->attach($orgs, ['joined_at' => now()->subDays(rand(0, 3))]);
        });

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
                        ->count(1),
                    'accounts'
                )
                ->create();
            $assetOrgUsers = $asset->org->users->shuffle();
            $requesterCount = rand(1, $assetOrgUsers->count());

            // TODO: Fix this
            $asset->users()->attach([
                ...$defaultUser->map(fn ($user) => [
                    'asset_id' => $asset->id,
                    'user_id' => $user->id,
                    'created_by' => 1,
                    'updated_by' => 1,
                    'role' => AssetAccessRole::REQUESTER->value,
                ])->all(),
                ...$defaultUser->map(fn ($user) => [
                    'asset_id' => $asset->id,
                    'user_id' => $user->id,
                    'created_by' => 1,
                    'updated_by' => 1,
                    'role' => AssetAccessRole::APPROVER->value,
                ])->all(),
                // ...$assetOrgUsers->take($requesterCount)
                //     ->map(fn ($user) => [
                //         'asset_id' => $asset->id,
                //         'user_id' => $user->id,
                //         'created_by' => 1,
                //         'updated_by' => 1,
                //         'role' => AssetAccessRole::REQUESTER->value,
                //     ])
                //     ->all(),
                // ...$assetOrgUsers->skip($requesterCount)
                //     ->map(fn ($user) => [
                //         'asset_id' => $asset->id,
                //         'user_id' => $user->id,
                //         'created_by' => 1,
                //         'updated_by' => 1,
                //         'role' => AssetAccessRole::APPROVER->value,
                //     ])
                //     ->all(),
            ]);
            $assets[] = $asset;
        }
    }
}
