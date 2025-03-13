<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\LoginResponse;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class FilamentLoginResponse extends LoginResponse
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        return redirect()->intended($request->query('back', '/'));
    }
}
