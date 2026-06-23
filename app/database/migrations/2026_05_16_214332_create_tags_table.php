<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('slug', 50)->unique();

            // Relación opcional con el usuario (null significa nativa/global)
            $table->foreignId('user_id')
              ->nullable()
              ->constrained()
              ->onDelete('cascade');

            $table->timestamps();

            // Índice único compuesto: evita que UN MISMO usuario duplique una etiqueta,
            // pero permite que diferentes usuarios tengan una etiqueta con el mismo nombre.
            $table->unique(['name', 'user_id']);
            $table->unique(['slug', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
