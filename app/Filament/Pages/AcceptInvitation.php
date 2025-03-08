<?php

namespace App\Filament\Pages;

use App\Actions\AddTeamMember;
use App\Actions\Cancelnvitation;
use App\Models\TeamInvitation;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SimplePage;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Locked;

class AcceptInvitation extends SimplePage
{
    protected static string $view = 'filament.pages.invitation.accept';

    protected static ?string $title = 'Aceitar Convite';

    #[Locked]
    public ?TeamInvitation $invitation;

    #[Locked]
    public ?User $user = null;

    #[Locked]
    public ?string $email;

    public ?string $name = null;

    public ?string $password = null;

    public ?string $passwordConfirmation = null;

    public function mount(TeamInvitation $invitation)
    {
        if ($invitation->team->hasUserWithEmail($invitation->email)) {
            return $this->redirect(Filament::getUrl($invitation->team));
        }

        $this->invitation = $invitation;
        $this->email = $invitation->email;
        $this->user = User::where('email', $invitation->email)->first();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->disabled(),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->maxLength(255)
                            ->autofocus()
                            ->required(),

                        Forms\Components\TextInput::make('password')
                            ->label('Senha')
                            ->password()
                            ->revealable()
                            ->rule(Password::default())
                            ->autocomplete('new-password')
                            ->same('passwordConfirmation'),

                        Forms\Components\TextInput::make('passwordConfirmation')
                            ->label('Confirmar senha')
                            ->password()
                            ->revealable()
                            ->required()
                            ->dehydrated(false),
                    ])
                    ->visible(fn () => ! $this->user),
            ]);
    }

    public function getHeading(): string
    {
        return $this->invitation->team->name;
    }

    public function getSubheading(): string
    {
        return 'Você foi convidado para participar desta equipe.';
    }

    public function acceptAction(): Action
    {
        return Action::make('accept')
            ->label('Aceitar convite')
            ->action('accept');
    }

    public function accept(): void
    {
        app(AddTeamMember::class)->add(
            team: $this->invitation->team,
            email: $this->invitation->email,
            attributes: [
                'name' => $this->name,
                'password' => $this->password,
            ]
        );

        app(Cancelnvitation::class)->cancel($this->invitation);

        $this->redirect(Filament::getUrl($this->invitation->team));
    }
}
