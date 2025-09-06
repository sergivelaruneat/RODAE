<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            $table->text('bio')->nullable();
            $table->string('sport')->nullable();
            $table->date('birthdate')->nullable();

            // Avatar guardado en BBDD como texto base64
            $table->longText('avatar_b64')->nullable();
            $table->string('avatar_mime', 80)->nullable();
            $table->unsignedInteger('avatar_size')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};

