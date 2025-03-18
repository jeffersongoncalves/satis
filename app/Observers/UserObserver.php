<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    public function created(User $user): void
    {
        $user->ownedTeams()->create([
            'name' => explode(' ', $user->name, 2)[0]."'s Team",
        ]);
    }
}
