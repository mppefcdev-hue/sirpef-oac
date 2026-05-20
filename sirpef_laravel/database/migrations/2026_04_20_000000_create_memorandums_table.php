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
        Schema::create('tbl_memorandums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('punto_cuenta_id')->constrained('tbl_punto_cuenta');
            $table->string('codigo')->unique();
            $table->string('de');
            $table->string('para');
            $table->string('asunto');
            $table->date('fecha');
            $table->text('cuerpo');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_memorandums');
    }
};
