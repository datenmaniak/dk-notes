<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//  Esta tabla no requiere campos
//  `created_at` ni `updated_at`.
//  Para maximizar el rendimiento del
//  filtrado dinámico, se definirá
// una **clave primaria compuesta**
// mediante `PRIMARY KEY(note_id, tag_id)`

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('note_tag', function (Blueprint $table) {
            // $table->id();
            // $table->timestamps();
            $table->foreignId('note_id')->constrained('notes')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            $table->primary(['note_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('note_tag');
    }
};
