<?php

namespace App\Filament\Pages\Tenancy;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\EditTenantProfile;
use Illuminate\Contracts\Support\Htmlable;

class EditTeam extends EditTenantProfile
{
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static string $layout = 'filament-panels::components.layout.simple';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->autofocus(),
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
