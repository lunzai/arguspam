<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\Request;
use App\Models\User;
use Illuminate\Console\Command;

class CreateFakeRequest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fake:request {asset : Asset ID} {user : User ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a fake request for a specific asset and user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $assetId = $this->argument('asset');
        $userId = $this->argument('user');

        $asset = Asset::find($assetId);
        $user = User::find($userId);

        if (!$asset || !$user) {
            $this->error('Asset or user not found');
            return 1;
        }

        $request = Request::factory()
            ->create([
                'org_id' => $asset->org_id,
                'asset_id' => $asset->id,
                'requester_id' => $user->id,
            ]);

        $this->info("Request created successfully: {$request->id}");
        return 0;
    }
}
