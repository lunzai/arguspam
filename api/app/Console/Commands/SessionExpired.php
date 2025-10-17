<?php

namespace App\Console\Commands;

use App\Models\Session;
use Illuminate\Console\Command;

class SessionExpired extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pam:session:expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find sessions that are expired and expire them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sessions = Session::scheduled()
            ->scheduledEndDateNowOrPast()
            ->get();
        $this->info("Found {$sessions->count()} expired sessions.");
        foreach ($sessions as $session) {
            try {
                $session->expire();
                $this->info("Expired session ID#{$session->id}.");
            } catch (\Exception $e) {
                $this->error("Failed to expire session ID#{$session->id}: {$e->getMessage()}");
            }
        }
    }
}
