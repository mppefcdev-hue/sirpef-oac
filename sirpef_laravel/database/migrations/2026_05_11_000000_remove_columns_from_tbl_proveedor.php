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
            // Drop foreign key constraints first
            $table->dropForeign(['registro_id']);
            $table->dropForeign(['memorandum_id']);

            // Drop columns
            $table->dropColumn(['registro_id', 'memorandum_id', 'monto']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_proveedor', function (Blueprint $table) {
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->foreign('registro_id')
                  ->references('id')
                  ->on('tbl_registros')
                  ->onDelete('cascade');

            $table->foreignId('memorandum_id')
                  ->nullable()
                  ->constrained('tbl_memorandums')
                  ->onDelete('cascade');
            
            $table->string('monto')->nullable();
        });
    }
};
