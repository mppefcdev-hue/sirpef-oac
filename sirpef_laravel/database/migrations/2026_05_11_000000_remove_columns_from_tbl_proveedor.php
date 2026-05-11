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
            // Drop foreign key constraints first if they exist
            if (Schema::hasColumn('tbl_proveedor', 'registro_id')) {
                try {
                    $table->dropForeign(['registro_id']);
                } catch (\Exception $e) {
                    // Constraint might not exist or have a different name
                }
            }

            if (Schema::hasColumn('tbl_proveedor', 'memorandum_id')) {
                try {
                    $table->dropForeign(['memorandum_id']);
                } catch (\Exception $e) {
                    // Constraint might not exist or have a different name
                }
            }

            // Drop columns if they exist
            $columnsToDrop = array_filter(['registro_id', 'memorandum_id', 'monto'], function($column) {
                return Schema::hasColumn('tbl_proveedor', $column);
            });

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
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
