<?php

namespace App\Filament\Pages\Tenancy;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\EditTenantProfile;
use Illuminate\Contracts\Support\Htmlable;

class EditTeam extends EditTenantProfile
{
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Nome do time')
                    ->description('O nome do time e informações adicionais.')
                    ->aside()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome do time')
                            ->required(),
                    ]),

                Forms\Components\Livewire::make(EditTeam\Components\Invitation::class),
                Forms\Components\Livewire::make(EditTeam\Components\PendingInvitations::class)->lazy(),
                Forms\Components\Livewire::make(EditTeam\Components\Members::class)->lazy(),
            ]);
    }

    public static function getLabel(): string
    {
        return 'Editar time';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Editar '.$this->tenant->name;
    }

    protected function afterSave()
    {
        $this->redirect(filament()->getUrl(tenant: $this->tenant));
    }
}
