<?php

namespace App\Observers;

use App\Models\Team;

class TeamObserver
{
    public function created(Team $team): void
    {
        $team->users()->attach($team->owner_id);
    }
}
