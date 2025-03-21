<?php

namespace App\Filament\Resources\PackageResource\Pages;

use App\Enums\PackageType;
use App\Filament\Resources\PackageResource;
use App\Filament\Resources\PackageResource\Forms\PackageResourceForm;
use App\Models\Package;
use App\Models\Team;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Gate;

use function App\Support\tenant;

class ManagePackages extends ManageRecords
{
    protected static string $resource = PackageResource::class;

    protected static string $view = 'filament.pages.infolist';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->slideOver()
                ->modalWidth('2xl')
                ->visible(
                    fn () => auth()->user()->ownedTeams->contains(tenant(Team::class)),
                ),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record(tenant(Team::class))
            ->schema([
                Infolists\Components\RepeatableEntry::make('packages')
                    ->label(false)
                    ->contained(false)
                    ->schema([
                        Infolists\Components\Section::make()
                            ->heading(fn (Package $record) => $record->name)
                            ->description(fn (Package $record) => $record->type->getLabel())
                            ->icon(fn (Package $record) => $record->type->getIcon())
                            ->columns(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('url')
                                    ->label('URL do Repositório')
                                    ->copyable(),

                                Infolists\Components\TextEntry::make('username')
                                    ->label('Username')
                                    ->getStateUsing('[Redacted]'),

                                Infolists\Components\TextEntry::make('password')
                                    ->label(
                                        fn (Package $record) => match ($record->type) {
                                            PackageType::Github => 'Personal Access Token',
                                            default => 'Senha',
                                        }
                                    )
                                    ->getStateUsing('[Redacted]'),
                            ])
                            ->headerActions([
                                Infolists\Components\Actions\Action::make('edit')
                                    ->label('Editar')
                                    ->icon('heroicon-o-pencil')
                                    ->link()
                                    ->slideOver()
                                    ->modalWidth('2xl')
                                    ->modalSubmitActionLabel('Salvar')
                                    ->fillForm(
                                        fn (Package $record): array => $record->attributesToArray(),
                                    )
                                    ->form(
                                        fn (Forms\Form $form) => PackageResourceForm::edit($form),
                                    )
                                    ->action(
                                        fn (Package $record, array $data) => $record->update($data),
                                    )
                                    ->visible(
                                        fn (Package $record) => Gate::allows('update', $record),
                                    ),

                                Infolists\Components\Actions\Action::make('delete')
                                    ->label('Excluir')
                                    ->icon('heroicon-o-trash')
                                    ->color('danger')
                                    ->link()
                                    ->requiresConfirmation()
                                    ->action(
                                        fn (Package $record) => $record->delete(),
                                    )
                                    ->visible(
                                        fn (Package $record) => Gate::allows('delete', $record),
                                    ),
                            ])
                            ->footerActions([
                                Infolists\Components\Actions\Action::make('versions')
                                    ->label('Histórico de Versões')
                                    ->icon('heroicon-o-arrow-top-right-on-square')
                                    ->link()
                                    ->url(
                                        url: fn (Package $record) => PackageResource::getUrl(name: 'versions', parameters: ['record' => $record]),
                                        shouldOpenInNewTab: true,
                                    ),
                            ]),
                    ]),
            ]);
    }
}
