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
    Schema::table('tbl_registros', function (Blueprint $table) {
        // Creamos la llave foránea hacia la tabla users
        $table->foreignId('user_id')
              ->nullable() // Se recomienda nullable si ya tienes registros viejos
              ->after('id') 
              ->constrained('users') // Referencia a la tabla de Breeze
              ->onDelete('cascade');
    });
}

public function down(): void
{
    Schema::table('tbl_registros', function (Blueprint $table) {
        $table->dropForeign(['user_id']);
        $table->dropColumn('user_id');
    });
}
};
