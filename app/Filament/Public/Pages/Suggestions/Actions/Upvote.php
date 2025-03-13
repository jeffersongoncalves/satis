<?php

namespace App\Filament\Public\Pages\Suggestions\Actions;

use App\Filament\Public\Pages\Suggestions;
use App\Models\Suggestion;
use Filament\Infolists\Components\Actions\Action;
use Filament\Support\Enums\ActionSize;

class Upvote extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'upvote';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Upvote')
            ->icon('heroicon-o-hand-thumb-up')
            ->color('gray');

        $this->badge(
            fn (Suggestion $record) => $record->votes_count
        );

        $this->action(
            function (Suggestions $livewire, Suggestion $record): void {
                $record->upvote(auth()->user());
                $livewire->dispatch('voted');
            }
        );

        $this->size(ActionSize::ExtraLarge);

        $this->visible(
            fn (Suggestion $record): bool => auth()->check() && $record->can_receive_votes && ! $record->upvoted(auth()->user())
        );
    }
}
