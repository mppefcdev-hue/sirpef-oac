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
            // Eliminar pago_id solo si existe
            if (Schema::hasColumn('tbl_pago_proveedor', 'pago_id')) {
                $table->dropForeign(['pago_id']);
                $table->dropColumn('pago_id');
            }

            // Agregar memorandum_id solo si NO existe
            if (!Schema::hasColumn('tbl_pago_proveedor', 'memorandum_id')) {
                $table->unsignedBigInteger('memorandum_id')->after('id');
                $table->foreign('memorandum_id')
                      ->references('id')
                      ->on('tbl_memorandums')
                      ->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_pago_proveedor', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_pago_proveedor', 'memorandum_id')) {
                $table->dropForeign(['memorandum_id']);
                $table->dropColumn('memorandum_id');
            }

            if (!Schema::hasColumn('tbl_pago_proveedor', 'pago_id')) {
                $table->unsignedBigInteger('pago_id')->after('id');
                $table->foreign('pago_id')
                      ->references('id')
                      ->on('tbl_pagos')
                      ->onDelete('cascade');
            }
        });
    }
};
