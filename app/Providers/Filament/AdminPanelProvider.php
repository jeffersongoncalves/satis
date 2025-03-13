<?php

namespace App\Providers\Filament;

use App\Filament\Pages;
use App\Filament\Pages\Tenancy\EditTeam;
use App\Filament\Pages\Tenancy\RegisterTeam;
use App\Http\Responses\FilamentLoginResponse;
use App\Models\Team;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile()
            ->tenant(Team::class)
            ->tenantRoutePrefix('teams')
            ->tenantRegistration(RegisterTeam::class)
            ->tenantProfile(EditTeam::class)
            ->topNavigation()
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->routes(function () {
                Route::get('teams/accept/{invitation}', Pages\AcceptInvitation::class)
                    ->middleware(['signed'])
                    ->name('team-invitations.accept');
            })
            ->discoverResources(
                in: app_path('Filament/Resources'),
                for: 'App\\Filament\\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/Pages'),
                for: 'App\\Filament\\Pages'
            )
            ->discoverWidgets(
                in: app_path('Filament/Widgets'),
                for: 'App\\Filament\\Widgets'
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                name: PanelsRenderHook::GLOBAL_SEARCH_AFTER,
                hook: fn () => view('filament.partials.github-repo-button'),
            )
            ->bootUsing(function (): void {
                $this->app->singleton(
                    \Filament\Http\Responses\Auth\Contracts\LoginResponse::class,
                    FilamentLoginResponse::class,
                );
            });
    }
}
