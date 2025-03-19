<?php

namespace App\Filament\Resources;

use App\Enums\PackageType;
use App\Filament\Resources\PackageResource\Pages;
use App\Models\Package;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;

use function App\Support\enum_equals;

class PackageResource extends Resource
{
    protected static ?string $model = Package::class;

    protected static ?string $modelLabel = 'Pacote';

    protected static ?string $pluralModelLabel = 'Pacotes';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(
                        fn (Forms\Get $get) => match ($get('type')) {
                            PackageType::Composer => 'vendor/package',
                            PackageType::Individual => 'Nome do Produto',
                            PackageType::Github => 'user/repo',
                        }
                    )
                    ->rule(
                        fn (Forms\Get $get): Closure => function (string $attribute, string $value, Closure $fail) use ($get) {
                            if ($get('type') === PackageType::Composer) {
                                if (preg_match('/^[a-z0-9-]+\/[a-z0-9-]+$/', $value)) {
                                    return;
                                }

                                $fail('O nome do pacote deve seguir o formato "vendor/package".');
                            }

                            if ($get('type') === PackageType::Github) {
                                if (preg_match('/^[a-z0-9-]+\/[a-z0-9-]+$/', $value)) {
                                    return;
                                }

                                $fail('O nome do pacote deve seguir o formato "user/repo".');
                            }
                        },
                    )
                    ->required(),

                Forms\Components\ToggleButtons::make('type')
                    ->hiddenLabel()
                    ->live()
                    ->options(PackageType::class)
                    ->default(PackageType::Composer)
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
                                fn (Forms\Get $get) => match ($get('type')) {
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
                                fn (Forms\Get $get) => match ($get('type')) {
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
                                fn (Forms\Get $get) => match ($get('type')) {
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
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePackages::route('/'),
            'versions' => Pages\ListPackageVersions::route('/{record}/versions'),
        ];
    }
}
