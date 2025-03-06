<x-filament-panels::page>
    <x-filament-panels::form id="form" wire:submit="save">
        {{ $this->form }}
    </x-filament-panels::form>
    
    <x-filament-panels::form id="inviteForm" wire:submit="invite">
        {{ $this->inviteForm }}
    </x-filament-panels::form>

    {{ $this->pendingInvitationsInfolist }}
    
    {{ $this->membersInfolist }}
</x-filament-panels::page>
