<?php

namespace App\Filament\Pages\Tenancy\EditTeam\Components;

use App\Models\Team;
use App\Models\TeamInvitation;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Livewire\Attributes\Locked;
use Livewire\Component;

use function App\Support\tenant;

/**
 * @property-read Forms\ComponentContainer $form
 */
class PendingInvitations extends Component implements HasForms, HasInfolists
{
    use InteractsWithForms;
    use InteractsWithInfolists;

    #[Locked]
    public ?Team $team = null;

    public array $data = [];

    public function mount(): void
    {
        $this->team = tenant(Team::class);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->team)
            ->schema([
                Infolists\Components\Section::make('Convites pendentes')
                    ->description('Essas pessoas foram convidadas para sua equipe e receberam um e-mail de convite. Elas podem se juntar à equipe aceitando o convite por e-mail.')
                    ->aside()
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('teamInvitations')
                            ->label(false)
                            ->columns(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('email')
                                    ->label(false),

                                Infolists\Components\Actions::make([])
                                    ->actions([
                                        Infolists\Components\Actions\Action::make('cancel-invitation')
                                            ->label('Cancelar')
                                            ->link()
                                            ->requiresConfirmation()
                                            ->action(function (TeamInvitation $record) {
                                                $record->delete();
                                            }),
                                    ])
                                    ->alignEnd(),
                            ]),
                    ]),
            ]);
    }

    public function render()
    {
        return <<<'HTML'
            <div>
                @if($this->team->teamInvitations->isNotEmpty())
                    {{ $this->infolist }}
                    <x-filament-actions::modals />
                @endif
            </div>
        HTML;
    }
}
