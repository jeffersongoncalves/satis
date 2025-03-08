<?php

namespace App\Models;

use App\Enums\LicenseType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class License extends Model
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
            'type' => LicenseType::class,
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
