<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Asegurar el path de menú 35 como 'CasesAdminFormPaso1'
        DB::table('menus')->where('id', 35)->update([
            'path' => 'CasesAdminFormPaso1',
            'updated_at' => now(),
        ]);

        // 2. Obtener todos los IDs de menús existentes para admin
        $allMenuIds = DB::table('menus')->pluck('id')->toArray();

        // 3. Actualizar Role 1 (admin)
        $roleAdmin = DB::table('roles')->where('id', 1)->first();
        if ($roleAdmin) {
            DB::table('roles')->where('id', 1)->update([
                'menu_ids' => json_encode(array_values($allMenuIds)),
                'updated_at' => now(),
            ]);
        }

        // 4. Actualizar Role 4 (User) con acceso a Monitoreo (1), Administración (33) y Formulario Paso 1 (35)
        $roleUser = DB::table('roles')->where('id', 4)->first();
        if ($roleUser) {
            $userMenuIds = [1, 33, 35];
            DB::table('roles')->where('id', 4)->update([
                'menu_ids' => json_encode(array_values($userMenuIds)),
                'updated_at' => now(),
            ]);
        }

        // 5. Actualizar Role 6 (Analista) con acceso a Monitoreo (1, 17), Administración (33), Pagos (34) y Formulario Paso 2 y 3 (39)
        $roleAnalista = DB::table('roles')->where('id', 6)->first();
        if ($roleAnalista) {
            $analistaMenuIds = [1, 17, 33, 34, 39];
            DB::table('roles')->where('id', 6)->update([
                'menu_ids' => json_encode(array_values($analistaMenuIds)),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op revert
    }
};
