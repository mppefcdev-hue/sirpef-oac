<?php
namespace App\Http\Services\User;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class getMe
{
    /**
     * Display a listing of the resource.
     *
     * @return array
     */
    static public function get()
    {
        $user = Auth::user();

        if (!$user) {
            return [
                'error' => 'Usuario no encontrado.',
            ];
        }

        $persona = $user->persona;
        $unid = ($persona && $persona->ministerio) ? $persona->ministerio->nombre : 'Sin ministerio';
        $nombre = $persona ? $persona->nombre_completo : $user->name;
        $cedula = $persona ? $persona->cedula : $user->cedula;

        $menuIds = json_decode($user->configUser?->menu_ids ?? '[]', true) ?: [];
        $menus = \App\Models\Menu::whereIn("id", $menuIds)->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'nombre' => $item->title,
                'path' => $item->path,
            ];
        });

        return [
            'id' => $user->id,
            'name' => $nombre,
            'email' => $user->email,
            'unid' => $unid,
            'role_id' => $user->role_id,
            'role' => $user->role ? ['id' => $user->role->id, 'name' => $user->role->name] : null,
            'cedula' => $cedula,
            'isAdmin' => $user->isAdmin(),
            'menus_id' => $menus,
        ];
    }
}