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
    Schema::table('config_users', function (Blueprint $table) {
        // Reemplaza 'nuevo_campo' por el nombre que desees
        $table->json('nuevo_campo')->nullable()->after('menu_ids');
    });
}

public function down(): void
{
    Schema::table('config_users', function (Blueprint $table) {
        $table->dropColumn('nuevo_campo');
    });
}
};
