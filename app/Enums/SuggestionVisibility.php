<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum SuggestionVisibility: string implements HasColor, HasIcon, HasLabel
{
    case Public = 'public';
    case Private = 'private';

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Public => 'heroicon-o-eye',
            self::Private => 'heroicon-o-eye-slash',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Public => 'success',
            self::Private => 'danger',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Public => 'Público',
            self::Private => 'Privado',
        };
    }
}
