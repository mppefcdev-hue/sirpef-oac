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
        Schema::table('tbl_proveedor', function (Blueprint $table) {
            // 1. Eliminar la llave foránea primero (importante el orden)
            $table->dropForeign(['registro_id']); 
            
            // 2. Eliminar las columnas
            $table->dropColumn(['monto', 'registro_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_proveedor', function (Blueprint $table) {
            // Revertir los cambios en caso de error
            $table->string('monto')->nullable();
            $table->unsignedBigInteger('registro_id')->nullable();

            $table->foreign('registro_id')
                  ->references('id')
                  ->on('tbl_registros')
                  ->onDelete('cascade');
        });
    }
};