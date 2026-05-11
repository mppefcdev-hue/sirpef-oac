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
            // Agregamos el campo cedula_rif
            $table->string('cedula_rif')->nullable()->after('id'); 
            
            // Si quieres que sea único para evitar duplicados reales:
            // $table->string('cedula_rif')->nullable()->unique()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_proveedor', function (Blueprint $table) {
            $table->dropColumn('cedula_rif');
        });
    }
};