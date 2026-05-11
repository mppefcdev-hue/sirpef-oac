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
        Schema::create('tbl_pagos', function (Blueprint $table) {
            $table->id();
            $table->string('orden_pago')->nullable();
            $table->date('fecha_orden_pago')->nullable();
            
            // Usamos decimal para precisión en montos de dinero
            $table->decimal('monto', 15, 2)->default(0);
            $table->text('descripcion')->nullable();
            $table->date('fecha_pago_financiero')->nullable();
            
            $table->decimal('saldo_deudor', 15, 2)->default(0);
            $table->decimal('saldo_acreedor', 15, 2)->default(0);
            $table->decimal('cuota_compromiso_disponible', 15, 2)->default(0);

            // --- RELACIONES / LLAVES FORÁNEAS ---

            // Conexión con Estatus de Pago (Nueva)
            $table->foreignId('estatus_pago_id')
                  ->nullable()
                  ->constrained('tbl_estatus_pagos')
                  ->onDelete('set null');

            // Conexión con la tabla tipo_pagos
            $table->foreignId('tipo_pago_id')
                  ->nullable()
                  ->constrained('tbl_tipo_pagos')
                  ->onDelete('set null');

            // Conexión con la tabla registros
            $table->foreignId('registro_id')
                  ->nullable()
                  ->constrained('tbl_registros') 
                  ->onDelete('set null'); 

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_pagos');
    }
};