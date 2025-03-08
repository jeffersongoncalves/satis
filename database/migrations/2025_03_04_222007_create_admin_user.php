<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        $credentials = match (app()->environment()) {
            'production' => [
                'name' => 'Admin',
                'email' => 'admin@filamentbr.com.br',
                'password' => '$2y$12$b.Wqe6s9L3t7gtcLzipfJOyhKiSoEmJfSYYhKMSAgo7uLPD15FHLa',
            ],
            default => [
                'name' => 'Saade',
                'email' => 'saade@laravel.local',
                'password' => Hash::make('123123123'),
            ],
        };

        tap(
            User::create($credentials),
            function (User $user): void {
                $user->ownedTeams()->create([
                    'name' => 'Comunidade',
                ]);
            }
        );
    }
};
