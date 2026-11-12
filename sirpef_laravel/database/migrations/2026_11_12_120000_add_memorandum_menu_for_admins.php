<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const MENU_PATH = 'oac.memos';

    /**
     * Crea el menú de Memorándum y lo asigna únicamente a los administradores.
     */
    public function up(): void
    {
        $menu = DB::table('menus')->where('path', self::MENU_PATH)->first();

        // Si ya existe un menú raíz con el mismo título, lo reutilizamos
        if (!$menu) {
            $menu = DB::table('menus')
                ->whereNull('menu_id')
                ->where('title', 'Memorándum')
                ->first();
        }

        if ($menu) {
            $menuId = (int) $menu->id;

            // Aseguramos que apunte al nombre de la ruta correcta
            if ($menu->path !== self::MENU_PATH) {
                DB::table('menus')->where('id', $menuId)->update([
                    'path' => self::MENU_PATH,
                    'updated_at' => now(),
                ]);
            }
        } else {
            $maxSort = (int) DB::table('menus')->whereNull('menu_id')->max('sort');

            $menuId = (int) DB::table('menus')->insertGetId([
                'title' => 'Memorándum',
                'path' => self::MENU_PATH,
                'icon' => 'envelope',
                'sort' => $maxSort + 1,
                'menu_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Solo el rol administrador (id 1)
        $this->addMenuToRole(1, $menuId);

        // Solo los usuarios administradores y sus configuraciones de menú
        $adminConfigUserIds = DB::table('users')
            ->where(function ($query) {
                $query->where('role_id', 1)->orWhere('is_admin', true);
            })
            ->whereNotNull('config_user_id')
            ->pluck('config_user_id')
            ->unique();

        foreach ($adminConfigUserIds as $configUserId) {
            $this->addMenuToConfigUser((int) $configUserId, $menuId);
        }
    }

    /**
     * Revierte el menú y lo elimina de roles y configuraciones de usuarios.
     */
    public function down(): void
    {
        $menu = DB::table('menus')->where('path', self::MENU_PATH)->first();

        if (!$menu) {
            return;
        }

        $menuId = (int) $menu->id;

        foreach (DB::table('roles')->get() as $role) {
            $menuIds = array_map('intval', json_decode($role->menu_ids ?? '[]', true) ?: []);

            if (in_array($menuId, $menuIds, true)) {
                $menuIds = array_values(array_filter($menuIds, fn ($id) => $id !== $menuId));

                DB::table('roles')->where('id', $role->id)->update([
                    'menu_ids' => json_encode($menuIds),
                    'updated_at' => now(),
                ]);
            }
        }

        foreach (DB::table('config_users')->get() as $configUser) {
            $menuIds = array_map('intval', json_decode($configUser->menu_ids ?? '[]', true) ?: []);

            if (in_array($menuId, $menuIds, true)) {
                $menuIds = array_values(array_filter($menuIds, fn ($id) => $id !== $menuId));

                DB::table('config_users')->where('id', $configUser->id)->update([
                    'menu_ids' => json_encode($menuIds),
                    'updated_at' => now(),
                ]);
            }
        }

        DB::table('menus')->where('id', $menuId)->delete();
    }

    private function addMenuToRole(int $roleId, int $menuId): void
    {
        $role = DB::table('roles')->where('id', $roleId)->first();

        if (!$role) {
            return;
        }

        $menuIds = array_map('intval', json_decode($role->menu_ids ?? '[]', true) ?: []);

        if (!in_array($menuId, $menuIds, true)) {
            $menuIds[] = $menuId;

            DB::table('roles')->where('id', $roleId)->update([
                'menu_ids' => json_encode(array_values(array_unique($menuIds))),
                'updated_at' => now(),
            ]);
        }
    }

    private function addMenuToConfigUser(int $configUserId, int $menuId): void
    {
        $configUser = DB::table('config_users')->where('id', $configUserId)->first();

        if (!$configUser) {
            return;
        }

        $menuIds = array_map('intval', json_decode($configUser->menu_ids ?? '[]', true) ?: []);

        if (!in_array($menuId, $menuIds, true)) {
            $menuIds[] = $menuId;

            DB::table('config_users')->where('id', $configUserId)->update([
                'menu_ids' => json_encode(array_values(array_unique($menuIds))),
                'updated_at' => now(),
            ]);
        }
    }
};
