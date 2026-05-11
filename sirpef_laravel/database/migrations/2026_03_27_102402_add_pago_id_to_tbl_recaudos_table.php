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
        Schema::table('tbl_recaudos', function (Blueprint $table) {
            // Agregamos la relación con la tabla de pagos
            $table->unsignedBigInteger('pago_id')->nullable()->after('registro_id');

            // Definimos la llave foránea
            $table->foreign('pago_id')
                  ->references('id')
                  ->on('tbl_pagos')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_recaudos', function (Blueprint $table) {
            // Eliminamos la llave foránea y luego la columna
            $table->dropForeign(['pago_id']);
            $table->dropColumn('pago_id');
        });
    }
};