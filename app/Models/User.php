<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Contracts\HasTeams;
use App\Observers\UserObserver;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasCurrentTenantLabel;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

#[ObservedBy(UserObserver::class)]
class User extends Authenticatable implements FilamentUser, HasCurrentTenantLabel, HasTenants, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    use HasTeams;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function suggestions(): HasMany
    {
        return $this->hasMany(Suggestion::class);
    }

    public function isAdmin(): bool
    {
        return $this->id === 1;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->allTeams()->isNotEmpty();
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return Gate::allows('view', $tenant);
    }

    public function getTenants(Panel $panel): array|Collection
    {
        if ($this->allTeams()->contains(Team::first())) {
            return $this->allTeams();
        }

        return $this->teams;
    }

    public function getCurrentTenantLabel(): string
    {
        return 'Time';
    }
}
