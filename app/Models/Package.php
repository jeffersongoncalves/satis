<?php

namespace App\Models;

use App\Enums\PackageType;
use App\Observers\PackageObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(PackageObserver::class)]
class Package extends Model
{
    protected $fillable = [
        'name',
        'type',
        'url',
        'username',
        'password',
    ];

    /**
     * Get the team that the package belongs to.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    protected function casts(): array
    {
        return [
            'type' => PackageType::class,
        ];
    }
}
