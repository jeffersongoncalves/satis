<?php

namespace App\Observers;

use App\Models\Team;
use App\Models\User;

class UserObserver
{
    public function created(User $user): void
    {
        tap(
            Team::forceCreate([
                'user_id' => $user->id,
                'name' => explode(' ', $user->name, 2)[0]."'s Team",
            ]),
            function (Team $team) use ($user) {
                $user->teams()->attach($team);
            }
        );
    }
}
