<?php

namespace App\Actions;

use App\Models\Team;
use App\Models\User;
use Closure;

class AddTeamMember
{
    /**
     * Add a new team member to the given team.
     */
    public function add(Team $team, string $email, array $attributes = []): void
    {
        $this->ensureUserIsNotAlreadyOnTeam($team, $email);

        $user = User::query()
            ->firstOrCreate(['email' => $email], $attributes);

        $team->users()->attach($user);
    }

    /**
     * Ensure that the user is not already on the team.
     */
    protected function ensureUserIsNotAlreadyOnTeam(Team $team, string $email): Closure
    {
        return function ($validator) use ($team, $email) {
            $validator->errors()
                ->addIf(
                    $team->hasUserWithEmail($email),
                    'email',
                    'Este usuário já está pertencendo ao time.'
                );
        };
    }
}
