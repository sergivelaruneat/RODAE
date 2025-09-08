<?php

// database/migrations/xxxx_add_snapshot_to_report_exercises.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('report_exercises', function (Blueprint $t) {
            $t->string('exercise_name')->nullable();
            $t->string('rest')->nullable();
            $t->string('series_reps')->nullable();
            $t->unsignedInteger('position')->nullable(); // opcional, útil para ordenar
        });
    }
    public function down(): void {
        Schema::table('report_exercises', function (Blueprint $t) {
            $t->dropColumn(['exercise_name','rest','series_reps','position']);
        });
    }
};
