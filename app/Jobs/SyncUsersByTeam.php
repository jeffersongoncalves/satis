<?php

namespace App\Jobs;

use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncUsersByTeam implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Team $team)
    {
        $this->team->load('users');
    }

    public function handle(): void
    {
        $this->team->users->each(function (User $user) {
            SyncUserPackages::dispatch($user);
        });
    }
}
