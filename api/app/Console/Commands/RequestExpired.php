<?php

namespace App\Console\Commands;

use App\Models\Request;
use Illuminate\Console\Command;

class RequestExpired extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pam:request:expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find requests that are expired and expire them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $requests = Request::pendingApproval()
            ->endDatetimeNowOrPast()
            ->get();
        $this->info("Found {$requests->count()} expired requests.");
        foreach ($requests as $request) {
            try {
                $request->expire();
                $this->info("Expired request ID#{$request->id}.");
            } catch (\Exception $e) {
                $this->error("Failed to expire request ID#{$request->id}: {$e->getMessage()}");
            }
        }
    }
}
