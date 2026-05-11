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
        Schema::table('tbl_memorandums', function (Blueprint $table) {
            // Drop columns if they exist
            $columnsToDrop = array_filter(['monto', 'proveedor'], function($column) {
                return Schema::hasColumn('tbl_memorandums', $column);
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
        Schema::table('tbl_memorandums', function (Blueprint $table) {
            $table->string('monto')->nullable();
            $table->string('proveedor')->nullable();
        });
    }
};
