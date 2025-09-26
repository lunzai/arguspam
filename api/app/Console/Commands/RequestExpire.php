<?php

namespace App\Console\Commands;

use App\Models\Request;
use Illuminate\Console\Command;

class RequestExpire extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'request:expire';

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
        $requests = Request::statusPending()
            ->endDatetimeNowOrPast()
            ->get();
        $this->info("Found {$requests->count()} expired requests.");
        foreach ($requests as $request) {
            $request->expire();
            $this->info("Expired request ID#{$request->id}.");
        }
    }
}
