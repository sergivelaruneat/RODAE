<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('publications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Título y deporte
            $table->string('title')->nullable();
            $table->string('sport')->nullable();

            // contenido + media
            $table->text('content');
            $table->string('media_url')->nullable();

            $table->timestamps();

            // Feed rápido por usuario/fecha
            $table->index(['user_id', 'created_at']);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('publications');
    }
};

