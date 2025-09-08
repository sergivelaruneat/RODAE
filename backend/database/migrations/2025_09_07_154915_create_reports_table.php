<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('routine_id')
                ->constrained()
                ->cascadeOnDelete();

            // Fecha/hora en la que se realizó el entrenamiento (opcional)
            $table->dateTime('performed_at')->nullable();

            $table->timestamps();

            // Índices útiles para dashboard/consultas
            $table->index(['user_id', 'performed_at']);
            $table->index('routine_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
