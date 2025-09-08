<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('report_exercises', function (Blueprint $table) {
            // Campos de snapshot
            if (!Schema::hasColumn('report_exercises', 'exercise_name')) {
                $table->string('exercise_name')->nullable();
            }
            if (!Schema::hasColumn('report_exercises', 'rest')) {
                $table->string('rest')->nullable();
            }
            if (!Schema::hasColumn('report_exercises', 'series_reps')) {
                $table->string('series_reps')->nullable();
            }
            if (!Schema::hasColumn('report_exercises', 'position')) {
                $table->unsignedInteger('position')->nullable();
            }

            // (Por si acaso) estos ya existían en tu schema; sólo añadir si faltaran
            if (!Schema::hasColumn('report_exercises', 'metric')) {
                $table->string('metric')->nullable();
            }
            if (!Schema::hasColumn('report_exercises', 'difficulty')) {
                $table->unsignedTinyInteger('difficulty')->nullable();
            }
            if (!Schema::hasColumn('report_exercises', 'completed')) {
                $table->boolean('completed')->default(false);
            }
        });
    }

    public function down(): void
    {
        // Borrado defensivo (si existen)
        Schema::table('report_exercises', function (Blueprint $table) {
            $cols = ['exercise_name','rest','series_reps','position','metric','difficulty','completed'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('report_exercises', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
