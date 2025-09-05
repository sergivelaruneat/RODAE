<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('publication_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_id')
                ->constrained('publications')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->timestamps();

            // Un usuario solo puede dar like una vez por publicación
            $table->unique(['publication_id', 'user_id']);

            // (Opcional) índices útiles
            $table->index('user_id');
            $table->index('publication_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publication_likes');
    }
};
