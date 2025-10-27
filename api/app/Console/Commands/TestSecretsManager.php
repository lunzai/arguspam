<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Services\Jit\Secrets\SecretsManager;
use Illuminate\Console\Command;

class TestSecretsManager extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:secretmanager {asset : The asset ID to test with}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function __construct(private SecretsManager $secretsManager)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $assetId = $this->argument('asset');
        $asset = Asset::findOrFail($assetId);
        $this->info('host: '.$asset->host);
        $this->info('port: '.$asset->port);
        $this->info('database: '.$asset->database);
        $this->info('username: '.$asset->adminAccount->username);
        $this->info('password: '.$asset->adminAccount->password);
        $this->info('Admin Cred: '.json_encode($this->secretsManager->getAdminCredentials($asset)));
        $databases = $this->secretsManager->getAllDatabases($asset);
        $this->info('Databases: '.json_encode($databases));
    }
}
