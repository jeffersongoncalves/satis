<?php

namespace App\Actions;

use App\Mail\TeamInvitation as TeamInvitationMail;
use App\Models\Team;
use App\Models\TeamInvitation;
use Closure;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class InviteTeamMember
{
    /**
     * Invite a new team member to the given team.
     */
    public function invite(Team $team, string $email): void
    {
        $this->validate($team, $email);

        $invitation = $team->teamInvitations()->create([
            'email' => $email,
        ]);

        Mail::to($email)->send(new TeamInvitationMail($invitation));
    }

    /**
     * Validate the invite member operation.
     */
    protected function validate(Team $team, string $email): void
    {
        Validator::make(
            data: [
                'email' => $email,
            ],
            rules: [
                'email' => [
                    'required', 'email',
                    Rule::unique(TeamInvitation::class)->where(function (Builder $query) use ($team) {
                        $query->where('team_id', $team->id);
                    }),
                ],
            ],
            messages: [
                'email.unique' => 'Este membro já foi convidado para o time.',
            ]
        )
            ->after(
                $this->ensureUserIsNotAlreadyOnTeam($team, $email)
            )
            ->validateWithBag('inviteTeamMember');
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
                    'O usuário já pertence ao time.'
                );
        };
    }
}
