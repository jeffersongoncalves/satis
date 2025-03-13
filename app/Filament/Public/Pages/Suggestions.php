<?php

namespace App\Filament\Public\Pages;

use App\Filament\Resources\SuggestionResource;
use App\Models\Suggestion;
use Filament\Infolists;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class Suggestions extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.infolist';

    protected $listeners = ['voted' => '$refresh'];

    public function getTitle(): string|Htmlable
    {
        return 'Sugestões';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Vote em qual próxima licença vamos adiquirir!';
    }

    protected function getHeaderActions(): array
    {
        return [
            SuggestionResource\Actions\CreateSuggestion::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $suggestions = Suggestion::public()
            ->withCount('votes')
            ->orderBy('votes_count', 'desc')
            ->get();

        return $infolist
            ->state([
                'suggestions' => $suggestions,
            ])
            ->schema([
                Infolists\Components\RepeatableEntry::make('suggestions')
                    ->label(false)
                    ->schema([
                        Infolists\Components\Split::make([
                            Infolists\Components\Group::make([
                                Infolists\Components\ImageEntry::make('image_url')
                                    ->label(false)
                                    ->extraImgAttributes([
                                        'class' => 'rounded-xl',
                                        'onerror' => sprintf("this.src = '%s'", asset('images/placeholder.svg')),
                                    ]),
                            ])->grow(false),

                            Infolists\Components\Group::make([
                                Infolists\Components\TextEntry::make('name')
                                    ->label(false)
                                    ->size(TextEntrySize::Large),

                                Infolists\Components\TextEntry::make('url')
                                    ->label(false)
                                    ->url(
                                        url: fn (string $state): string => $state,
                                        shouldOpenInNewTab: true
                                    ),
                            ])->extraAttributes(['class' => '[&_.fi-fo-component-ctn]:gap-0']),

                            Infolists\Components\Actions::make([])
                                ->actions([
                                    Suggestions\Actions\LoginToUpvote::make(),
                                    Suggestions\Actions\Upvote::make(),
                                    Suggestions\Actions\Unvote::make(),
                                ])
                                ->alignEnd()
                                ->verticallyAlignCenter(),
                        ]),
                    ]),
            ]);
    }
}
