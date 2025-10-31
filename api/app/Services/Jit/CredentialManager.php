<?php

namespace App\Services\Jit;

use App\Exceptions\CredentialNotFoundException;
use App\Models\Asset;
use App\Services\Jit\Repositories\Contracts\AssetRepositoryInterface;

class CredentialManager
{
    public function __construct(
        private AssetRepositoryInterface $assetRepository,
        private UserCreationValidator $validator,
        private \App\Services\Jit\Database\DatabaseDriverFactory $driverFactory,
        private array $config = []
    ) {}

    public function getAdminCredentials(Asset $asset): array
    {
        $adminAccount = $this->assetRepository->getActiveAdminAccount($asset);
        if (!$adminAccount) {
            throw new CredentialNotFoundException('No active admin account found for asset: '.$asset->name);
        }

        return [
            'username' => $adminAccount->username,
            'password' => $adminAccount->password,
            'databases' => $adminAccount->databases,
        ];
    }

    public function generateCredentials(Asset $asset): array
    {
        try {
            $adminAccount = $this->assetRepository->getActiveAdminAccount($asset);
            $driver = $this->driverFactory->create();
            $this->validator->validateUserCreation($asset, $adminAccount);
            return $driver->generateSecureCredentials();
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to get admin credentials: '.$e->getMessage(), 0, $e);
        }
    }
}


