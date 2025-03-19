<?php

namespace App\Filament\Resources\PackageResource\Pages;

use App\Filament\Resources\PackageResource;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;

class ListPackageVersions extends ViewRecord
{
    protected static string $resource = PackageResource::class;

    protected static ?string $title = 'Histórico de Versões';

    protected static ?string $breadcrumb = 'Versões';

    protected static string $view = 'filament.pages.infolist';

    public function infolist(Infolist $infolist): Infolist
    {
        $versions = $this->getVersions();

        return $infolist
            ->state($versions)
            ->columns(1)
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

                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\View::make('filament-tables::components.empty-state.index')
                            ->viewData([
                                'heading' => 'Nenhuma versão encontrada',
                                'description' => 'O pacote pode não ter sido indexado ainda.',
                                'icon' => 'heroicon-o-archive-box-x-mark',
                            ]),
                    ])
                    ->visible(
                        fn () => empty($versions['versions'])
                    ),
            ]);
    }

    private function getVersions(): array
    {
        return Cache::remember("package-{$this->record->id}-versions", now()->addMinutes(30), function (): array {
            $file = rescue(
                fn (): array => app(Filesystem::class)->json(storage_path("app/private/satis/p2/{$this->record->name}.json")),
                fn (): array => []
            );

            return [
                'versions' => collect($file['packages'][$this->record->name] ?? [])
                    ->sortByDesc('version_normalized')
                    ->select(['version', 'time'])
                    ->toArray(),
            ];
        });
    }
}
