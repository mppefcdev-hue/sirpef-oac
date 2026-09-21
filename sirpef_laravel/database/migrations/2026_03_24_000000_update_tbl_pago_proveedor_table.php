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
        Schema::table('tbl_pago_proveedor', function (Blueprint $table) {
            // Eliminar la llave foránea y la columna pago_id
            $table->dropForeign(['pago_id']);
            $table->dropColumn('pago_id');

            // Agregar la columna memorandum_id y su llave foránea
            $table->unsignedBigInteger('memorandum_id')->after('id');
            $table->foreign('memorandum_id')
                  ->references('id')
                  ->on('tbl_memorandums')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_pago_proveedor', function (Blueprint $table) {
            $table->dropForeign(['memorandum_id']);
            $table->dropColumn('memorandum_id');

            $table->unsignedBigInteger('pago_id')->after('id');
            $table->foreign('pago_id')
                  ->references('id')
                  ->on('tbl_pagos')
                  ->onDelete('cascade');
        });
    }
};
