<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Elimina telefono_2 de tbl_personas y agrega telefono a tbl_proveedor.
     */
    public function up(): void
    {
        // 1. Agregar campo telefono a tbl_proveedor
        if (!Schema::hasColumn('tbl_proveedor', 'telefono')) {
            Schema::table('tbl_proveedor', function (Blueprint $table) {
                $table->string('telefono', 255)->nullable()->after('cedula_rif');
            });
        }

        // 2. Eliminar campo telefono_2 de tbl_personas
        if (Schema::hasColumn('tbl_personas', 'telefono_2')) {
            Schema::table('tbl_personas', function (Blueprint $table) {
                $table->dropColumn('telefono_2');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurar telefono_2 en tbl_personas
        if (!Schema::hasColumn('tbl_personas', 'telefono_2')) {
            Schema::table('tbl_personas', function (Blueprint $table) {
                $table->string('telefono_2', 255)->nullable();
            });
        }

        // Eliminar telefono de tbl_proveedor
        if (Schema::hasColumn('tbl_proveedor', 'telefono')) {
            Schema::table('tbl_proveedor', function (Blueprint $table) {
                $table->dropColumn('telefono');
            });
        }
    }
};
