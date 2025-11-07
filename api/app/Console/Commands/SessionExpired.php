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
        $scheduledSessions = Session::scheduled()
            ->scheduledEndDateNowOrPast()
            ->get();
        $this->info("Found {$scheduledSessions->count()} expired scheduled sessions.");
        foreach ($scheduledSessions as $session) {
            try {
                $session->expire();
                $this->info("Expired session ID#{$session->id}.");
            } catch (\Exception $e) {
                $this->error("Failed to expire session ID#{$session->id}: {$e->getMessage()}");
            }
        }
        $startedSessions = Session::started()
            ->scheduledEndDateNowOrPast()
            ->get();
        $this->info("Found {$startedSessions->count()} expired started sessions.");
        foreach ($startedSessions as $session) {
            try {
                $session->terminate();
                $this->info("Terminated session ID#{$session->id}.");
            } catch (\Exception $e) {
                $this->error("Failed to terminate session ID#{$session->id}: {$e->getMessage()}");
            }
        }
    }
}
