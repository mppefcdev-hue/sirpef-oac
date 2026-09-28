<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Modificar el menú 35 actual a 'formulario paso 1'
        $menuPaso1 = DB::table('menus')->where('id', 35)->first();
        if ($menuPaso1) {
            DB::table('menus')->where('id', 35)->update([
                'title' => 'formulario paso 1',
                'path'  => 'CasesAdminFormPaso1',
                'updated_at' => now(),
            ]);
        }

        // 2. Crear el menú 'formulario paso 2 y 3' si no existe
        $menuPaso2 = DB::table('menus')
            ->where('menu_id', 33)
            ->where('title', 'formulario paso 2 y 3')
            ->first();

        $menuPaso2Id = null;
        if (!$menuPaso2) {
            $menuPaso2Id = DB::table('menus')->insertGetId([
                'title' => 'formulario paso 2 y 3',
                'path'  => 'CasesAdminFormPaso2',
                'icon'  => 'icon',
                'sort'  => 1,
                'menu_id' => 33,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $menuPaso2Id = $menuPaso2->id;
        }

        // 3. Asignar el nuevo menú a los usuarios y roles que ya tenían el paso 1
        $paso1Id = $menuPaso1 ? $menuPaso1->id : 35;
        if ($menuPaso2Id) {
            $configUsers = DB::table('config_users')->get();
            foreach ($configUsers as $cu) {
                $menuIds = json_decode($cu->menu_ids, true) ?? [];
                if (in_array($paso1Id, $menuIds) && !in_array($menuPaso2Id, $menuIds)) {
                    $menuIds[] = $menuPaso2Id;
                    DB::table('config_users')
                        ->where('id', $cu->id)
                        ->update([
                            'menu_ids' => json_encode(array_values($menuIds)),
                            'updated_at' => now()
                        ]);
                }
            }

            $roles = DB::table('roles')->get();
            foreach ($roles as $role) {
                $menuIds = json_decode($role->menu_ids, true) ?? [];
                if (in_array($paso1Id, $menuIds) && !in_array($menuPaso2Id, $menuIds)) {
                    $menuIds[] = $menuPaso2Id;
                    DB::table('roles')
                        ->where('id', $role->id)
                        ->update([
                            'menu_ids' => json_encode(array_values($menuIds)),
                            'updated_at' => now()
                        ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir nombre del menú 35 a 'formulario'
        DB::table('menus')->where('id', 35)->update([
            'title' => 'formulario',
            'path'  => 'CasesAdminForm',
            'updated_at' => now(),
        ]);

        // Eliminar el menú 'formulario paso 2 y 3'
        $menuPaso2 = DB::table('menus')
            ->where('menu_id', 33)
            ->where('title', 'formulario paso 2 y 3')
            ->first();

        if ($menuPaso2) {
            $configUsers = DB::table('config_users')->get();
            foreach ($configUsers as $cu) {
                $menuIds = json_decode($cu->menu_ids, true) ?? [];
                if (in_array($menuPaso2->id, $menuIds)) {
                    $menuIds = array_filter($menuIds, fn($id) => $id != $menuPaso2->id);
                    DB::table('config_users')
                        ->where('id', $cu->id)
                        ->update([
                            'menu_ids' => json_encode(array_values($menuIds)),
                            'updated_at' => now()
                        ]);
                }
            }

            DB::table('menus')->where('id', $menuPaso2->id)->delete();
        }
    }
};
