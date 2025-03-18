<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        $credentials = [
            'name' => 'Admin',
            'email' => 'admin@filamentphp.com.br',
            'password' => match (app()->environment()) {
                'production' => '$2y$12$b.Wqe6s9L3t7gtcLzipfJOyhKiSoEmJfSYYhKMSAgo7uLPD15FHLa',
                default => Hash::make('password'),
            },
        ];

        tap(
            User::create($credentials),
            function (User $user): void {
                $user->touch('email_verified_at');
            }
        );
    }
};
