<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('followed_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->timestamps();

            // Un seguidor no puede seguir 2 veces al mismo usuario
            $table->unique(['follower_id', 'followed_id']);

            // (Opcional) índices para consultas laterales
            $table->index('follower_id');
            $table->index('followed_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follows');
    }
};
