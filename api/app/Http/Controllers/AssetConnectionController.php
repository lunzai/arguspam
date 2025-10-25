<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Services\Jit\Secrets\SecretsManager;
use Illuminate\Http\Response;

class AssetConnectionController extends Controller
{
    protected $secretsManager;

    public function __construct(SecretsManager $secretsManager)
    {
        $this->secretsManager = $secretsManager;
    }

    /**
     * Test admin connection
     */
    public function show(Asset $asset): Response
    {
        $this->secretsManager
            ->getDatabaseDriver($asset);
        return $this->ok();
    }

}
