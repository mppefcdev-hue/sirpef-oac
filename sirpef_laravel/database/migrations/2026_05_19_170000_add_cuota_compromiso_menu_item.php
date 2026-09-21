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
        // 1. Obtener o crear el menú padre "Administración"
        $parentMenu = DB::table('menus')->where('path', '/casos/administracion')->first();

        if (!$parentMenu) {
            $parentId = DB::table('menus')->insertGetId([
                'title' => 'Administración',
                'path' => '/casos/administracion',
                'icon' => 'icon',
                'sort' => 0,
                'menu_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $parentId = $parentMenu->id;
        }

        // 2. Definir los ítems de submenú a insertar
        $submenus = [
            [
                'title' => 'Casos Administración',
                'path' => 'casesAdminIndex',
                'icon' => 'icon',
                'sort' => 1,
                'menu_id' => $parentId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Formulario Administración',
                'path' => 'CasesAdminForm',
                'icon' => 'icon',
                'sort' => 2,
                'menu_id' => $parentId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Cuota de Compromiso',
                'path' => 'casesAdminCuotas',
                'icon' => 'icon',
                'sort' => 3,
                'menu_id' => $parentId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        $insertedMenuIds = [];

        foreach ($submenus as $menu) {
            // Verificar si la ruta ya existe para no duplicar
            $existing = DB::table('menus')->where('path', $menu['path'])->first();
            if (!$existing) {
                $insertedMenuIds[] = DB::table('menus')->insertGetId($menu);
            } else {
                $insertedMenuIds[] = $existing->id;
            }
        }

        // 3. Asignar los nuevos IDs de menú a config_users para roles 1 (Admin/Dev) y 2 (Director)
        $users = DB::table('users')
            ->whereIn('role_id', [1, 2])
            ->whereNotNull('config_user_id')
            ->get();

        foreach ($users as $user) {
            $configUser = DB::table('config_users')
                ->where('id', $user->config_user_id)
                ->first();

            if ($configUser) {
                $menuIds = json_decode($configUser->menu_ids, true) ?? [];
                
                // Unir los menús sin duplicar
                $updatedMenuIds = array_unique(array_merge($menuIds, [$parentId], $insertedMenuIds));

                DB::table('config_users')
                    ->where('id', $user->config_user_id)
                    ->update([
                        'menu_ids' => json_encode(array_values($updatedMenuIds)),
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $paths = ['casesAdminIndex', 'CasesAdminForm', 'casesAdminCuotas'];

        // Obtener los IDs de las rutas agregadas
        $menuIdsToDelete = DB::table('menus')
            ->whereIn('path', $paths)
            ->pluck('id')
            ->toArray();

        if (!empty($menuIdsToDelete)) {
            // Eliminar de los config_users
            $configUsers = DB::table('config_users')->get();
            foreach ($configUsers as $cu) {
                $menuIds = json_decode($cu->menu_ids, true) ?? [];
                $filteredIds = array_filter($menuIds, fn($id) => !in_array($id, $menuIdsToDelete));

                DB::table('config_users')
                    ->where('id', $cu->id)
                    ->update([
                        'menu_ids' => json_encode(array_values($filteredIds)),
                        'updated_at' => now(),
                    ]);
            }

            // Eliminar los registros de la tabla menus
            DB::table('menus')->whereIn('id', $menuIdsToDelete)->delete();
        }
    }
};