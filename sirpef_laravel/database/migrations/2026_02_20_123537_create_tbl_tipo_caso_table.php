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
        // Usamos Schema::table porque estamos agregando columnas a una tabla existente
        Schema::table('tbl_tipo_caso', function (Blueprint $table) {
            // Agregamos la columna y la relación
            $table->foreignId('ministerio_id')
                  ->nullable()
                  ->constrained('tb_ministerio') 
                  ->onDelete('cascade');
            
            // Si la tabla no tenía timestamps, déjalos. 
            // Si ya los tenía, elimina esta línea para evitar error de columna duplicada.
            // $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_tipo_caso', function (Blueprint $table) {
            // 1. Eliminamos la llave foránea primero
            $table->dropForeign(['ministerio_id']);
            
            // 2. Luego eliminamos la columna
            $table->dropColumn('ministerio_id');
            
            // Nota: He quitado dropSoftDeletes() porque no agregaste 
            // $table->softDeletes() en el método up().
        });
    }
};