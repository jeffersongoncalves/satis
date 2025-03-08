<?php

namespace App\Actions;

use App\Models\Team;
use App\Models\TeamInvitation;

class Cancelnvitation
{
    /**
     * Cancel the given team invitation.
     */
    public function cancel(TeamInvitation $invitation): void
    {
        $invitation->delete();
    }
}
