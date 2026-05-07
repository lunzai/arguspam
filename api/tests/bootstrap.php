<?php

// Clear cached config before any tests run.
// This prevents config:cache from baking in the production database name,
// which would cause RefreshDatabase to wipe the wrong (production) database.
$configCache = __DIR__.'/../bootstrap/cache/config.php';
if (file_exists($configCache)) {
    unlink($configCache);
}

require_once __DIR__.'/../vendor/autoload.php';
