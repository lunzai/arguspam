<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearOpcache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'opcache:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears PHP OPcache';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!function_exists('opcache_reset')) {
            $this->error('OPcache is not enabled or opcache_reset() is disabled.');
            return Command::FAILURE;
        }

        if (opcache_reset()) {
            $this->info('OPcache has been successfully cleared.');
            return Command::SUCCESS;
        } else {
            $this->error('Failed to clear OPcache.');
            return Command::FAILURE;
        }
    }
}
