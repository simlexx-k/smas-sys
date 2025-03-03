<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

class SystemHealthService
{
    public function checkAll(): array
    {
        return [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue(),
        ];
    }

    private function checkDatabase(): string
    {
        try {
            DB::connection()->getPdo();
            return 'healthy';
        } catch (\Exception $e) {
            return 'error';
        }
    }

    private function checkCache(): string
    {
        try {
            Cache::store()->has('health-check');
            return 'healthy';
        } catch (\Exception $e) {
            return 'error';
        }
    }

    private function checkStorage(): string
    {
        try {
            Storage::disk('local')->put('health-check.txt', 'ok');
            Storage::disk('local')->delete('health-check.txt');
            return 'healthy';
        } catch (\Exception $e) {
            return 'error';
        }
    }

    private function checkQueue(): string
    {
        try {
            Queue::size();
            return 'healthy';
        } catch (\Exception $e) {
            return 'error';
        }
    }
} 