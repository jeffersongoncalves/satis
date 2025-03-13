<?php

namespace App\Filament\Public\Pages\Suggestions\Actions;

use App\Filament\Public\Pages\Suggestions;
use App\Models\Suggestion;
use Filament\Infolists\Components\Actions\Action;
use Filament\Support\Enums\ActionSize;

class Unvote extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'unvote';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Upvoted')
            ->icon('heroicon-s-hand-thumb-up')
            ->color('primary');

        $this->badge(
            fn (Suggestion $record) => $record->votes_count
        );

        $this->action(
            function (Suggestions $livewire, Suggestion $record): void {
                $record->downvote(auth()->user());
                $livewire->dispatch('voted');
            }
        );

        $this->size(ActionSize::ExtraLarge);

        $this->visible(
            fn (Unvote $action, Suggestion $record): bool => auth()->check() && $record->upvoted(auth()->user())
        );
    }
}
