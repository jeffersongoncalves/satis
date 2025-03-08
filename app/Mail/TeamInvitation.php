<?php

namespace App\Mail;

use App\Models\TeamInvitation as TeamInvitationModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class TeamInvitation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The team invitation instance.
     *
     * @var \App\Models\TeamInvitation
     */
    public $invitation;

    /**
     * Create a new message instance.
     */
    public function __construct(TeamInvitationModel $invitation)
    {
        $this->invitation = $invitation;
    }

    /**
     * Build the message.
     */
    public function build(): static
    {
        return $this
            ->markdown(
                view: 'emails.team-invitation',
                data: ['acceptUrl' => URL::signedRoute('filament.admin.team-invitations.accept', ['invitation' => $this->invitation])]
            )
            ->subject('Convidado para o time');
    }
}
