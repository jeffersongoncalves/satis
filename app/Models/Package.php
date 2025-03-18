<?php

namespace App\Models;

use App\Enums\PackageType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Package extends Model
{
    protected $fillable = [
        'name',
        'type',
        'url',
        'username',
        'password',
    ];

    protected function casts(): array
    {
        return [
            'type' => PackageType::class,
        ];
    }

    /**
     * Get the team that the package belongs to.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
