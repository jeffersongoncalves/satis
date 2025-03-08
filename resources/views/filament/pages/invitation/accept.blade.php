<x-filament-panels::page.simple>
    <x-filament-panels::form id="form" wire:submit="accept">
        {{ $this->form }}

        {{ $this->acceptAction }}
    </x-filament-panels::form>
</x-filament-panels::page.simple>
