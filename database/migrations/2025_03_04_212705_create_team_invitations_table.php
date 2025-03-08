<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_invitations', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();

            $table->unique(['team_id', 'email']);
            $table->timestamps();
        });
    }
};
