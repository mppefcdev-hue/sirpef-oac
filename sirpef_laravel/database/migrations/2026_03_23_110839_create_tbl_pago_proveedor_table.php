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
        Schema::create('tbl_pago_proveedor', function (Blueprint $table) {
            $table->id();
            
            // Relación con la tabla de Memorandums
            $table->foreignId('memorandum_id')
                  ->constrained('tbl_memorandums')
                  ->onDelete('cascade');

            // Relación con la tabla de Proveedores
            $table->foreignId('proveedor_id')
                  ->constrained('tbl_proveedor')
                  ->onDelete('cascade');

        
            // Campo adicional: Cuánto de la orden de pago corresponde a este proveedor
            $table->decimal('monto_relacionado', 15, 2)->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_pago_proveedor');
    }
};
