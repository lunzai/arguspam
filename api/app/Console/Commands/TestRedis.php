<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class TestRedis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Redis connection';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Redis connection...');

        try {
            // Test ping
            $ping = Redis::ping();
            $this->info("✓ Redis Ping: {$ping}");

            // Test basic operations
            Redis::set('test:string', 'Hello World');
            $value = Redis::get('test:string');
            $this->info("✓ String operations: {$value}");

            // Test Laravel Cache
            Cache::put('test:cache', 'Laravel Cache Test', 60);
            $cacheValue = Cache::get('test:cache');
            $this->info("✓ Laravel Cache: {$cacheValue}");

            // Test different data types
            Redis::lpush('test:list', 'item1', 'item2');
            $listLength = Redis::llen('test:list');
            $this->info("✓ List operations: {$listLength} items");

            Redis::hset('test:hash', 'field1', 'value1');
            $hashValue = Redis::hget('test:hash', 'field1');
            $this->info("✓ Hash operations: {$hashValue}");

            // Test expiration
            Redis::setex('test:expire', 5, 'expires in 5 seconds');
            $ttl = Redis::ttl('test:expire');
            $this->info("✓ Expiration: TTL = {$ttl} seconds");

            // Performance test
            $start = microtime(true);
            for ($i = 0; $i < 1000; $i++) {
                Redis::set("perf:test:{$i}", "value{$i}");
            }
            $end = microtime(true);
            $time = round(($end - $start) * 1000, 2);
            $this->info("✓ Performance: 1000 SET operations in {$time}ms");

            // Cleanup
            Redis::del(['test:string', 'test:list', 'test:hash', 'test:expire']);
            Redis::eval("
                local keys = redis.call('keys', 'perf:test:*')
                if #keys > 0 then
                    return redis.call('del', unpack(keys))
                end
                return 0
            ", []);
            Cache::forget('test:cache');

            $this->info('✓ All tests passed! Redis is working correctly.');

        } catch (\Exception $e) {
            $this->error('✗ Redis test failed: '.$e->getMessage());
            return 1;
        }

    }
}
