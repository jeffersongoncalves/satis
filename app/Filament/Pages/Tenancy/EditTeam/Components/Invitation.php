<?php

namespace App\Filament\Pages\Tenancy\EditTeam\Components;

use App\Actions\Jetstream\InviteTeamMember;
use App\Models\Team;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Alignment;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

use function App\Support\tenant;

/**
 * @property-read Forms\ComponentContainer $form
 */
class Invitation extends Component implements HasForms
{
    use InteractsWithForms;

    #[Locked]
    public ?Team $team = null;

    public array $data = [];

    public function mount(): void
    {
        $this->team = tenant(Team::class);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Adicionar membro')
                    ->description('Adicionar um novo membro ao time, permitindo que ele acesse as licenças e recursos do time.')
                    ->aside()
                    ->columns(2)
                    ->schema([
                        Forms\Components\Placeholder::make('add-member-instructions')
                            ->label('Por favor, insira o endereço de e-mail da pessoal que você deseja adicionar ao time. O convite será enviado automaticamente.'),

                        Forms\Components\TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required(),
                    ])
                    ->footerActions([
                        Forms\Components\Actions\Action::make('invite-member')
                            ->label('Enviar convite')
                            ->action('invite'),
                    ])
                    ->footerActionsAlignment(Alignment::End),
            ])
            ->statePath('data');
    }

    public function invite(): void
    {
        $data = $this->form->getState();

        try {
            app(InviteTeamMember::class)->invite(
                user: auth()->user(),
                team: $this->team,
                email: $data['email'],
                role: 'user',
            );

            Notification::make()
                ->title('Convite enviado')
                ->body(sprintf('O convite foi enviado para o email %s.', $data['email']))
                ->success()
                ->send();

        } catch (ValidationException $exception) {
            Notification::make()
                ->title('Erro ao enviar convite')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    public function render()
    {
        return '{{ $this->form }}';
    }
}
