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
        Schema::table('tbl_cuotas_compromiso', function (Blueprint $table) {
            $table->decimal('monto_acumulado_anterior', 15, 2)->default(0)->after('monto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_cuotas_compromiso', function (Blueprint $table) {
            $table->dropColumn('monto_acumulado_anterior');
        });
    }
};
