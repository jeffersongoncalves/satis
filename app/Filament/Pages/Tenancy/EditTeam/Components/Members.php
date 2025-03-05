<?php

namespace App\Filament\Pages\Tenancy\EditTeam\Components;

use App\Models\Team;
use App\Models\User;
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
class Members extends Component implements HasForms, HasInfolists
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
                Infolists\Components\Section::make('Membros do time')
                    ->description('Todas as pessoas que fazem parte do time.')
                    ->aside()
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('users')
                            ->label(false)
                            ->columns(2)
                            ->schema([
                                // TODO: Add User card custom component
                                Infolists\Components\Split::make([])
                                    ->schema([
                                        Infolists\Components\ImageEntry::make('avatar')
                                            ->label(false)
                                            ->circular()
                                            ->size(60)
                                            ->state('https://ui-avatars.com/api/?name=S&color=FFFFFF&background=09090b')
                                            ->grow(false),

                                        Infolists\Components\TextEntry::make('name')
                                            ->label(false),
                                    ]),

                                Infolists\Components\Actions::make([])
                                    ->actions([
                                        Infolists\Components\Actions\Action::make('remove-member')
                                            ->label('Remover')
                                            ->link()
                                            ->requiresConfirmation()
                                            ->action(function (User $record) {
                                                $record->teams()->detach($this->team);
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
                @if($this->team->users->isNotEmpty())
                    {{ $this->infolist }}
                    <x-filament-actions::modals />
                @endif
            </div>
        HTML;
    }
}
