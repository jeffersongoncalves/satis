<?php

namespace App\Jobs;

use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncUsersByTeam implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

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
