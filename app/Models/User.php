<?php

namespace App\Models;

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
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;

#[ObservedBy(UserObserver::class)]
class User extends Authenticatable implements FilamentUser, HasCurrentTenantLabel, HasTenants, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    use HasTeams;
    use Notifiable;
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

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

    public function packages(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations($this->teams(), (new Team)->packages());
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
        return $this->allTeams();
    }

    public function getCurrentTenantLabel(): string
    {
        return 'Time';
    }
}
