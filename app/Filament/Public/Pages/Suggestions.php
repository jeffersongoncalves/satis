<?php

namespace App\Filament\Public\Pages;

use App\Models\Suggestion;
use Filament\Infolists;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Filament\Support\Enums\ActionSize;
use Illuminate\Contracts\Support\Htmlable;

class Suggestions extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.infolist';

    public function getTitle(): string|Htmlable
    {
        return 'Sugestões';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Vote em qual próxima licença vamos adiquirir!';
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $suggestions = Suggestion::query()
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
                                        'onerror' => "this.src = 'https://www.svgrepo.com/show/508699/landscape-placeholder.svg'",
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
                                    Infolists\Components\Actions\Action::make('upvote')
                                        ->icon('heroicon-o-hand-thumb-up')
                                        ->color('gray')
                                        ->badge(
                                            fn (Suggestion $record) => $record->votes_count
                                        )
                                        ->size(ActionSize::ExtraLarge)
                                        ->action(
                                            fn (Suggestion $record) => $record->upvote(auth()->user())
                                        )
                                        ->visible(
                                            fn (Suggestion $record) => auth()->check() && ! $record->upvoted(auth()->user())
                                        ),

                                    Infolists\Components\Actions\Action::make('downvote')
                                        ->icon('heroicon-o-hand-thumb-down')
                                        ->badge(
                                            fn (Suggestion $record) => $record->votes()->count()
                                        )
                                        ->size(ActionSize::ExtraLarge)
                                        ->action(
                                            fn (Suggestion $record) => $record->downvote(auth()->user())
                                        )
                                        ->visible(
                                            fn (Suggestion $record) => auth()->check() && $record->upvoted(auth()->user())
                                        ),
                                ])
                                ->alignEnd()
                                ->verticallyAlignCenter(),
                        ]),
                    ]),
            ]);
    }
}
