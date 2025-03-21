<?php

namespace App\Filament\Resources\PackageResource\Forms;

use App\Enums\PackageType;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;

use function App\Support\enum_equals;

abstract class PackageResourceForm
{
    public static function create(Form $form): Form
    {
        return $form
            ->schema([
                static::getNameFormComponent(),
                static::getTypeFormComponent(),
                Forms\Components\Fieldset::make()
                    ->columns(2)
                    ->schema([
                        static::getComposerInstructionsFormComponent(),
                        static::getGithubInstructionsFormComponent(),
                        static::getUrlFormComponent()->columnSpan(2),
                        static::getUsernameFormComponent(),
                        static::getPasswordFormComponent(),
                    ]),
            ]);
    }

    public static function edit(Form $form): Form
    {
        return $form
            ->schema([
                static::getNameFormComponent(),
                Forms\Components\Fieldset::make()
                    ->columns(2)
                    ->schema([
                        static::getComposerInstructionsFormComponent(),
                        static::getGithubInstructionsFormComponent(),
                        static::getUrlFormComponent()->columnSpan(2),
                        static::getUsernameFormComponent(),
                        static::getPasswordFormComponent(),
                    ]),
            ]);
    }

    public static function getNameFormComponent(): Forms\Components\Component
    {
        return Forms\Components\TextInput::make('name')
            ->label(
                fn (Forms\Get $get) => match (PackageType::of($get('type'))) {
                    PackageType::Composer => 'vendor/package',
                    PackageType::Github => 'user/repo',
                }
            )
            ->rule(
                fn (Forms\Get $get): Closure => function (string $attribute, string $value, Closure $fail) use ($get) {
                    if (enum_equals($get('type'), PackageType::Composer)) {
                        if (preg_match('/^[a-z0-9-]+\/[a-z0-9-]+$/', $value)) {
                            return;
                        }

                        $fail('O nome do pacote deve seguir o formato "vendor/package".');
                    }

                    if (enum_equals($get('type'), PackageType::Github)) {
                        if (preg_match('/^[a-z0-9-]+\/[a-z0-9-]+$/', $value)) {
                            return;
                        }

                        $fail('O nome do pacote deve seguir o formato "user/repo".');
                    }
                },
            )
            ->required();
    }

    public static function getTypeFormComponent(): Forms\Components\Component
    {
        return Forms\Components\ToggleButtons::make('type')
            ->hiddenLabel()
            ->live()
            ->options(PackageType::class)
            ->default(PackageType::Composer)
            ->required();
    }

    public static function getComposerInstructionsFormComponent(): Forms\Components\Component
    {
        return Forms\Components\Placeholder::make('composer-instructions')
            ->label('Configurações do Composer')
            ->content('Para adicionar um pacote do tipo Composer, você deve informar as credenciais de acesso ao repositório privado. O produto será sub-licenciado usando o Satis. Cada membro do time receberá uma credencial de acesso individual. Não compartilhe essas credenciais com ninguém.')
            ->visible(fn (Forms\Get $get): bool => enum_equals($get('type'), PackageType::Composer));
    }

    public static function getGithubInstructionsFormComponent(): Forms\Components\Component
    {
        return Forms\Components\Placeholder::make('github-instructions')
            ->label('Configurações do GitHub')
            ->content('Para adicionar um pacote do tipo GitHub, você deve informar as credenciais de acesso ao repositório privado. O produto será sub-licenciado usando o GitHub Packages. Cada membro do time receberá um Personal Access Token (PAT). Não compartilhe essas credenciais com ninguém.')
            ->visible(fn (Forms\Get $get): bool => enum_equals($get('type'), PackageType::Github));
    }

    public static function getUrlFormComponent(): Forms\Components\Component
    {
        return Forms\Components\TextInput::make('url')
            ->label(
                fn (Forms\Get $get) => match (PackageType::of($get('type'))) {
                    PackageType::Composer => 'URL do Repositório Composer',
                    PackageType::Github => 'URL SSH do Repositório',
                }
            )
            ->rule(
                fn (Forms\Get $get): Closure => function (string $attribute, string $value, Closure $fail) use ($get) {
                    if (! enum_equals($get('type'), PackageType::Github)) {
                        return filter_var($value, FILTER_VALIDATE_URL);
                    }

                    if (preg_match('/^git@github.com:/', $value)) {
                        return;
                    }

                    $fail('Utilize uma URL SSH válida para o repositório do GitHub.');
                },
            )
            ->required();
    }

    public static function getUsernameFormComponent(): Forms\Components\Component
    {
        return Forms\Components\TextInput::make('username')
            ->label(
                fn (Forms\Get $get) => match (PackageType::of($get('type'))) {
                    PackageType::Composer => 'Username do Composer',
                    PackageType::Github => 'Username ou Organização do GitHub',
                }
            )
            ->required();
    }

    public static function getPasswordFormComponent(): Forms\Components\Component
    {
        return Forms\Components\TextInput::make('password')
            ->label(
                fn (Forms\Get $get) => match (PackageType::of($get('type'))) {
                    PackageType::Composer => 'Password do Composer',
                    PackageType::Github => 'Personal Access Token (PAT)',
                }
            )
            ->password()
            ->revealable()
            ->rule(
                fn (Forms\Get $get): Closure => function (string $attribute, string $value, Closure $fail) use ($get) {
                    if (! enum_equals($get('type'), PackageType::Github)) {
                        return;
                    }

                    if (preg_match('/^github_pat_/', $value)) {
                        return;
                    }

                    $fail('O Personal Access Token (PAT) deve seguir o formato "github_pat_".');
                },
            )
            ->required();
    }
}
