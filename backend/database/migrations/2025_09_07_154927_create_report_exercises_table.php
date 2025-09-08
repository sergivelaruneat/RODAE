<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('report_exercises', function (Blueprint $table) {
            $table->id();

            $table->foreignId('report_id')
                ->constrained('reports')
                ->cascadeOnDelete();

            // Ejercicio de la rutina al que hace referencia este resultado
            $table->foreignId('routine_exercise_id')
                ->constrained('routine_exercises')
                ->cascadeOnDelete();

            // Métrica libre (p.ej. "3x10 40 kg", "100 libres", "5 km 25:30")
            $table->string('metric')->nullable();

            // Dificultad 0–10 (ajústalo a tu escala)
            $table->unsignedTinyInteger('difficulty')->default(0);

            // Marcado como completado o no
            $table->boolean('completed')->default(false);

            $table->timestamps();

            $table->index(['report_id', 'routine_exercise_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_exercises');
    }
};
