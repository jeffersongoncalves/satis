<?php

namespace App\Filament\Pages;

use App\Models\Package;
use App\Models\Team;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Locked;

use function App\Support\tenant;

class PackageVersions extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.package-versions';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'packages/{package}/versions';

    #[Locked]
    public ?Team $record = null;

    public ?Package $package = null;

    public function mount(): void
    {
        $this->record = tenant(Team::class);
    }

    public function getTitle(): string
    {
        return $this->package->name;
    }

    public function getSubheading(): string
    {
        return 'Histórico de Versões';
    }

    public function packageVersionsInfolist(Infolist $infolist): Infolist
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
        return Cache::remember("package-{$this->package->id}-versions", now()->addHour(), function (): array {
            $file = app(Filesystem::class)->json(storage_path("app/private/satis/p2/{$this->package->name}.json"));

            return [
                'versions' => collect($file['packages'][$this->package->name])
                    ->sortByDesc('version_normalized')
                    ->select(['version', 'time'])
                    ->toArray(),
            ];
        });
    }
}
