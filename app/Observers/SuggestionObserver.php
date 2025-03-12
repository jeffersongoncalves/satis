<?php

namespace App\Observers;

use App\Models\Suggestion;

class SuggestionObserver
{
    public function created(Suggestion $suggestion): void
    {
        $suggestion->fetchImage();
    }

    public function updated(Suggestion $suggestion): void
    {
        $suggestion->fetchImage();
    }
}
