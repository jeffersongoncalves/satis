<?php

namespace App\Filament\Resources\PackageResource\Pages;

use App\Enums\PackageType;
use App\Filament\Resources\PackageResource;
use App\Models\Package;
use App\Models\Team;
use Closure;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ManageRecords;

use function App\Support\enum_equals;
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
                ->modalWidth('2xl'),
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
                                    ->label(
                                        fn (Package $record) => match ($record->type) {
                                            PackageType::Composer => 'URL do Repositório',
                                            PackageType::Individual => 'URL do Produto',
                                            PackageType::Github => 'URL do Repositório',
                                        }
                                    )
                                    ->copyable(),

                                Infolists\Components\TextEntry::make('username')
                                    ->label(
                                        fn (Package $record) => match ($record->type) {
                                            PackageType::Individual => 'Email',
                                            default => 'Username',
                                        }
                                    )
                                    ->copyable()
                                    ->getStateUsing(
                                        fn (Package $record) => match ($record->type) {
                                            PackageType::Individual => $record->username,
                                            default => '[Redacted]',
                                        }
                                    )
                                    ->visible(
                                        fn (Package $record) => match ($record->type) {
                                            PackageType::Github => false,
                                            default => true,
                                        }
                                    ),

                                Infolists\Components\TextEntry::make('password')
                                    ->label(
                                        fn (Package $record) => match ($record->type) {
                                            PackageType::Github => 'Personal Access Token',
                                            default => 'Senha',
                                        }
                                    )
                                    ->copyable()
                                    ->getStateUsing(
                                        fn (Package $record) => match ($record->type) {
                                            PackageType::Individual => $record->password,
                                            default => '[Redacted]',
                                        }
                                    ),
                            ])
                            ->headerActions([
                                Infolists\Components\Actions\Action::make('edit')
                                    ->label('Editar')
                                    ->icon('heroicon-o-pencil')
                                    ->link()
                                    ->slideOver()
                                    ->modalWidth('2xl')
                                    ->modalSubmitActionLabel('Salvar')
                                    ->form([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nome')
                                            ->required(),

                                        Forms\Components\Fieldset::make()
                                            ->columns(2)
                                            ->schema([
                                                Forms\Components\Placeholder::make('composer-instructions')
                                                    ->label('Configurações do Composer')
                                                    ->content('Para adicionar um pacote do tipo Composer, você deve informar as credenciais de acesso ao repositório privado. O produto será sub-licenciado usando o Satis. Cada membro do time receberá uma credencial de acesso individual. Não compartilhe essas credenciais com ninguém.')
                                                    ->visible(fn (Forms\Get $get): bool => enum_equals($get('type'), PackageType::Composer)),

                                                Forms\Components\Placeholder::make('individual-instructions')
                                                    ->label('Configurações Individuais')
                                                    ->content('Para adicionar um pacote do tipo Individual, você deve informar as credenciais de acesso ao produto. Cada membro do time receberá uma credencial de acesso individual. Não compartilhe essas credenciais com ninguém.')
                                                    ->visible(fn (Forms\Get $get): bool => enum_equals($get('type'), PackageType::Individual)),

                                                Forms\Components\Placeholder::make('github-instructions')
                                                    ->label('Configurações do GitHub')
                                                    ->content('Para adicionar um pacote do tipo GitHub, você deve informar a URL SSH do repositório privado e um Fine-grained Personal Access Token (PAT). Cada membro do time receberá uma credencial de acesso individual.')
                                                    ->visible(fn (Forms\Get $get): bool => enum_equals($get('type'), PackageType::Github)),

                                                Forms\Components\TextInput::make('url')
                                                    ->label(
                                                        fn (Forms\Get $get) => match (PackageType::from($get('type'))) {
                                                            PackageType::Composer => 'URL do Repositório Composer',
                                                            PackageType::Individual => 'URL do Produto',
                                                            PackageType::Github => 'URL SSH do Repositório',
                                                        }
                                                    )
                                                    ->rule(
                                                        fn (Forms\Get $get): Closure => function (string $attribute, string $value, Closure $fail) use ($get) {
                                                            if ($get('type') !== PackageType::Github) {
                                                                return filter_var($value, FILTER_VALIDATE_URL);
                                                            }

                                                            if (preg_match('/^git@github.com:/', $value)) {
                                                                return;
                                                            }

                                                            $fail('Utilize uma URL SSH válida para o repositório do GitHub.');
                                                        },
                                                    )
                                                    ->required()
                                                    ->columnSpan(2),

                                                Forms\Components\TextInput::make('username')
                                                    ->label(
                                                        fn (Forms\Get $get) => match (PackageType::from($get('type'))) {
                                                            PackageType::Composer => 'Username do Composer',
                                                            PackageType::Individual => 'Email de Acesso',
                                                        }
                                                    )
                                                    ->required()
                                                    ->visible(
                                                        fn (Forms\Get $get): bool => ! enum_equals($get('type'), PackageType::Github)
                                                    )
                                                    ->columnStart(1),

                                                Forms\Components\TextInput::make('password')
                                                    ->label(
                                                        fn (Forms\Get $get) => match (PackageType::from($get('type'))) {
                                                            PackageType::Composer => 'Password do Composer',
                                                            PackageType::Individual => 'Senha de Acesso',
                                                            PackageType::Github => 'Personal Access Token (PAT)',
                                                        }
                                                    )
                                                    ->password()
                                                    ->revealable()
                                                    ->rule(
                                                        fn (Forms\Get $get): Closure => function (string $attribute, string $value, Closure $fail) use ($get) {
                                                            if ($get('type') !== PackageType::Github) {
                                                                return;
                                                            }

                                                            if (preg_match('/^github_pat_/', $value)) {
                                                                return;
                                                            }

                                                            $fail('O Personal Access Token (PAT) deve seguir o formato "github_pat_".');
                                                        },
                                                    )
                                                    ->required(),
                                            ]),
                                    ])
                                    ->fillForm(
                                        fn (Package $record): array => $record->attributesToArray(),
                                    )
                                    ->action(
                                        fn (Package $record, array $data) => $record->update($data),
                                    ),

                                Infolists\Components\Actions\Action::make('delete')
                                    ->label('Excluir')
                                    ->icon('heroicon-o-trash')
                                    ->color('danger')
                                    ->link()
                                    ->requiresConfirmation()
                                    ->action(
                                        fn (Package $record) => $record->delete(),
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
                                    )
                                    ->visible(
                                        fn (Package $record) => match ($record->type) {
                                            PackageType::Individual => false,
                                            default => true,
                                        }
                                    ),
                            ]),
                    ]),
            ]);
    }
}
