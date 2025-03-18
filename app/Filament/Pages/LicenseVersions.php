<?php

namespace App\Filament\Pages;

use App\Models\License;
use App\Models\Team;
use Filament\Actions\Action;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Locked;

use function App\Support\tenant;

class LicenseVersions extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.license-versions';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'licenses/{license}/versions';

    #[Locked]
    public ?Team $record = null;

    public ?License $license = null;

    public function mount(): void
    {
        $this->record = tenant(Team::class);
    }

    public function getSubheading(): string
    {
        return 'Histórico de Versões';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->icon('heroicon-o-arrow-left')
                ->link()
                ->url(ManageLicenses::getUrl()),
        ];
    }

    public function licenseVersionsInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->state($this->getVersions())
            ->schema([
                Infolists\Components\RepeatableEntry::make('versions')
                    ->label(false)
                    ->contained(false)
                    ->schema([
                        Infolists\Components\Section::make()
                            ->columns()
                            ->schema([
                                Infolists\Components\TextEntry::make('version')
                                    ->label('Versão')
                                    ->badge(),
                                Infolists\Components\TextEntry::make('time')
                                    ->label('Data de Publicação')
                                    ->dateTime('M j, Y H:i'),
                            ]),
                    ]),
            ]);
    }

    private function getVersions(): array
    {
        return Cache::remember("license-{$this->license->id}-versions", now()->addHour(), function (): array {
            $file = app(Filesystem::class)->json("satis/p2/{$this->license->name}.json");

            return [
                'versions' => collect($file['packages'][$this->license->name])
                    ->sortByDesc('version_normalized')
                    ->select(['version', 'time'])
                    ->toArray(),
            ];
        });
    }
}
