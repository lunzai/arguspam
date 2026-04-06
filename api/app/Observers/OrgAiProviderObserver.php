<?php

namespace App\Observers;

use App\Models\OrgAiProvider;
use Laravel\Ai\Ai;

class OrgAiProviderObserver
{
    public function saved(OrgAiProvider $orgAiProvider): void
    {
        Ai::purge(OrgAiProvider::configNameFor($orgAiProvider->org_id, $orgAiProvider->id));
    }

    public function deleted(OrgAiProvider $orgAiProvider): void
    {
        Ai::purge(OrgAiProvider::configNameFor($orgAiProvider->org_id, $orgAiProvider->id));
    }
}
