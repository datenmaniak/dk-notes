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
        Schema::table('categories', function (Blueprint $table) {
            // foreignId crea un BIGINT UNSIGNED compatible con el ID de usuarios.
            // constrained() crea la llave foránea automáticamente.
            // onDelete('cascade') asegura que si se borra un usuario, se borren sus categorías.
            $table->foreignId('user_id')
                ->nullable() // Evita errores si ya existen filas viejas
                ->constrained()
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Es vital eliminar primero la restricción de llave foránea antes de la columna
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
