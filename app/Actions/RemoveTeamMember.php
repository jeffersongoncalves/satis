<?php

namespace App\Actions;

use App\Models\Team;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class RemoveTeamMember
{
    /**
     * Remove the team member from the given team.
     */
    public function remove(Team $team, User $teamMember): void
    {
        $this->ensureUserDoesNotOwnTeam($teamMember, $team);

        $team->removeUser($teamMember);
    }

    /**
     * Ensure that the currently authenticated user does not own the team.
     */
    protected function ensureUserDoesNotOwnTeam(User $teamMember, Team $team): void
    {
        if ($teamMember->id === $team->owner->id) {
            throw ValidationException::withMessages(
                messages: ['team' => [__('You may not leave a team that you created.')]]
            )->errorBag('removeTeamMember');
        }
    }
}
