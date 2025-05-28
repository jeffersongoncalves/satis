<?php

namespace App\Console\Commands;

use App\Jobs\SyncUserPackages;
use App\Models\User;
use Illuminate\Console\Command;

class SatisBuild extends Command
{
    protected $signature = 'satis:build';

    protected $description = 'Builds the Satis repository for all teams';

    public function handle(): int
    {
        $users = User::query()
            ->whereHas('packages')
            ->get();

        foreach ($users as $user) {
            SyncUserPackages::dispatch($user);
        }

        return self::SUCCESS;
    }
}
