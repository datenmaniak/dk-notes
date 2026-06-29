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
            // 1. Remover la restricción que está rompiendo el sistema
            $table->dropUnique('categories_name_unique');

            // Si tu columna 'slug' también tenía unique() original, remuévela aquí:
            $table->dropUnique('categories_slug_unique');

            // 2. Crear los nuevos índices compuestos multiusuario
            $table->unique(['name', 'user_id']);
            $table->unique(['slug', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Revertir el proceso
            $table->dropUnique(['name', 'user_id']);
            $table->dropUnique(['slug', 'user_id']);

            $table->string('name')->unique()->change();
        });
    }
};
