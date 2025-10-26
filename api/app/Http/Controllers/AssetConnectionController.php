<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Services\Jit\JitManager;
use Illuminate\Http\Response;

class AssetConnectionController extends Controller
{
    protected $jitManager;

    public function __construct(JitManager $jitManager)
    {
        $this->jitManager = $jitManager;
    }

    /**
     * Test admin connection
     */
    public function show(Asset $asset): Response
    {
        $adminCredentials = $this->jitManager->getAdminCredentials($asset);
        $testResult = $this->jitManager
            ->testConnection($asset, [
                'password' => $adminCredentials['password'],
                'username' => $adminCredentials['username'],
            ]);
        if (!$testResult) {
            throw new \Exception('Failed to test connection');
        }
        return $this->ok();
    }

    public function index(Asset $asset)
    {
        $databases = $this->jitManager->getAllDatabases($asset);
        return [
            'data' => $databases,
        ];
    }
}
