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
        // 1. Insert the new menu item under Administracion (menu_id = 33)
        $menuId = DB::table('menus')->insertGetId([
            'title' => 'cuota de compromiso',
            'path' => 'casesAdminCuotas',
            'icon' => 'icon',
            'sort' => 0,
            'menu_id' => 33,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Add the new menu ID to the config_users menu_ids array for roles 1 (Admin/Dev) and 2 (Director)
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
                if (!in_array($menuId, $menuIds)) {
                    $menuIds[] = $menuId;
                    DB::table('config_users')
                        ->where('id', $user->config_user_id)
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
        $menuItem = DB::table('menus')
            ->where('path', 'casesAdminCuotas')
            ->where('menu_id', 33)
            ->first();

        if ($menuItem) {
            // Remove the menu ID from all config_users records
            $configUsers = DB::table('config_users')->get();
            foreach ($configUsers as $cu) {
                $menuIds = json_decode($cu->menu_ids, true) ?? [];
                if (in_array($menuItem->id, $menuIds)) {
                    $menuIds = array_filter($menuIds, fn($id) => $id != $menuItem->id);
                    DB::table('config_users')
                        ->where('id', $cu->id)
                        ->update([
                            'menu_ids' => json_encode(array_values($menuIds)),
                            'updated_at' => now()
                        ]);
                }
            }

            // Delete the menu record
            DB::table('menus')->where('id', $menuItem->id)->delete();
        }
    }
};
