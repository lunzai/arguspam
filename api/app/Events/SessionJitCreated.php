<?php

namespace App\Events;

use App\Models\AssetAccount;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessionJitCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public AssetAccount $assetAccount) {}
}
