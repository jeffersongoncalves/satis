<?php

namespace App\Filament\Pages;

use App\Enums\PackageType;
use App\Models\Package;
use App\Models\Team;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;

use function App\Support\enum_equals;
use function App\Support\tenant;

class ManagePackages extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static string $view = 'filament.pages.packages';

    protected static ?string $slug = 'packages';

    #[Locked]
    public ?Team $record = null;

    public array $data = [];

    public function mount()
    {
        $this->record = tenant(Team::class);
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        if (Gate::denies('create', Package::class)) {
            return $form;
        }

        return $form
            ->model($this->record)
            ->schema([
                Forms\Components\Section::make('Adicionar Pacote')
                    ->columns(2)
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
                            ->label('Tipo')
                            ->live()
                            ->options(PackageType::class)
                            ->default(PackageType::Composer)
                            ->required(),

                        Forms\Components\Fieldset::make()
                            ->columns(3)
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
                    ])
                    ->footerActions([
                        Forms\Components\Actions\Action::make('create')
                            ->label('Adicionar')
                            ->action(
                                function (Team $record) {
                                    $package = $record->packages()->create($this->form->getState());
                                    $this->form->fill();

                                    return $package;
                                }
                            ),
                    ]),
            ])
            ->statePath('data');
    }

    public function packagesInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->record)
            ->schema([
                Infolists\Components\RepeatableEntry::make('packages')
                    ->label(false)
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
                                Infolists\Components\Actions\Action::make('package-versions')
                                    ->label('Histórico de Versões')
                                    ->icon('heroicon-o-arrow-top-right-on-square')
                                    ->link()
                                    ->url(
                                        url: fn (Package $record) => PackageVersions::getUrl(['package' => $record->id]),
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

    public function getTitle(): string
    {
        return 'Gerenciar Pacotes';
    }

    public function getHeading(): string
    {
        return '';
    }

    public static function getNavigationLabel(): string
    {
        return 'Gerenciar Pacotes';
    }
}
