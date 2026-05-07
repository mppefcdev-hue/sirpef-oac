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
            if (!Schema::hasColumn('tbl_proveedor', 'memorandum_id')) {
                $table->foreignId('memorandum_id')
                      ->nullable()
                      ->constrained('tbl_memorandums')
                      ->onDelete('cascade');
            }

            if (!Schema::hasColumn('tbl_proveedor', 'monto')) {
                $table->decimal('monto', 15, 2)->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_proveedor', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_proveedor', 'memorandum_id')) {
                $table->dropForeign(['memorandum_id']);
                $table->dropColumn('memorandum_id');
            }
            
            if (Schema::hasColumn('tbl_proveedor', 'monto')) {
                $table->dropColumn('monto');
            }
        });
    }
};
