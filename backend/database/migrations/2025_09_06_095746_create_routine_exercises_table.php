<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routine_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routine_id')->constrained()->onDelete('cascade');

            $table->string('name', 150);
            $table->text('details');                 // API: "description"
            $table->text('series_reps')->nullable(); // texto libre: "3x10", "100 libres"
            $table->string('rest', 80)->nullable();  // texto libre: "90s", "2–3 min"

            $table->unsignedInteger('position');
            $table->timestamps();

            $table->index(['routine_id','position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routine_exercises');
    }
};
