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
        Schema::create('tbl_tipo_pagos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->nullable();      // Opcional
            $table->text('descripcion')->nullable();   // Opcional
            $table->timestamps();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('tbl_tipo_pagos');
    }
};
