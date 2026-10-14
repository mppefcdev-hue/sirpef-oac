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
        Schema::create('tbl_bitacora_pagos', function (Blueprint $table) {
            $table->id();

            // Relación con el registro (caso general)
            $table->unsignedBigInteger('registro_id');
            $table->foreign('registro_id')->references('id')->on('tbl_registros')->onDelete('cascade');

            // Relación opcional con el pago
            $table->unsignedBigInteger('pago_id')->nullable();
            $table->foreign('pago_id')->references('id')->on('tbl_pagos')->onDelete('cascade');

            // Usuario que redacta la nota
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Rol del usuario al momento de escribir la nota ('User', 'Analista', 'Admin', 'Director', etc.)
            $table->string('rol_nombre', 100)->nullable();

            // Canal / Tipo de contacto ('Llamada telefónica', 'Correo electrónico', 'WhatsApp / Mensajería', 'Visita presencial', 'Otro')
            $table->string('tipo_contacto', 100)->nullable()->default('Llamada telefónica');

            // Contacto o persona del proveedor contactada (opcional)
            $table->string('contacto_proveedor')->nullable();

            // Detalle o descripción de lo conversado / acuerdos alcanzados
            $table->text('descripcion');

            // Fecha en que ocurrió el contacto
            $table->timestamp('fecha_contacto')->useCurrent();

            $table->timestamps();

            // Índices para búsquedas rápidas
            $table->index('registro_id');
            $table->index('pago_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_bitacora_pagos');
    }
};
