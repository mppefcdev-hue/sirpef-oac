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
        try {
            if (!Schema::hasColumn('tbl_pago_proveedor', 'memorandum_id')) {
                Schema::table('tbl_pago_proveedor', function (Blueprint $table) {
                    $table->unsignedBigInteger('memorandum_id')->nullable()->after('id');
                });
            }
        } catch (\Throwable $e) {
            // Ignorar si ya existe o no se puede alterar
        }
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
