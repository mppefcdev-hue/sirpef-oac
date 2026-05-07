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
        Schema::create('tbl_proveedor', function (Blueprint $table) {
            $table->id();
            $table->string('rif')->unique();
            $table->string('nombre_comercial');
            $table->string('razon_social');
            $table->string('direccion')->nullable();
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->string('contacto_persona')->nullable();
            $table->decimal('monto', 15, 2)->default(0);
            $table->foreignId('registro_id')->nullable()->constrained('tbl_registros')->onDelete('cascade');
            $table->foreignId('memorandum_id')->nullable()->constrained('tbl_memorandums')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_proveedor');
    }
};
