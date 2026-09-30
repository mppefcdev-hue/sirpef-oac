<?php
namespace App\Http\Services\User;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Models\Persona;
use App\Models\ConfigUser;

class UpdateUserService
{
    static public function execute(UpdateUserRequest $request, User $user) : JsonResponse
    {
        // Validar los datos de la solicitud
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8',
            'role_id' => 'required|integer',
            'cedula' => 'required|integer|unique:users,cedula,' . $user->id,
            'menus_id' => 'nullable|array',
            'is_admin' => 'nullable|boolean',
            'eventos_id' => 'nullable|array',
            'oficinas_ids' => 'nullable|array',
        ]);

        // Expandir IDs de menús padre para asegurar su visualización
        $menusId = $validatedData['menus_id'] ?? [];
        if (!empty($menusId)) {
            $parentIds = \App\Models\Menu::whereIn('id', $menusId)->pluck('menu_id')->filter()->unique()->toArray();
            while (!empty($parentIds)) {
                $menusId = array_unique(array_merge($menusId, $parentIds));
                $parentIds = \App\Models\Menu::whereIn('id', $parentIds)->pluck('menu_id')->filter()->unique()->toArray();
            }
            $menusId = array_values($menusId);
        }

        // Actualizar ConfigUser
        $configUser = $user->configUser;
        if (!$configUser) {
            $configUser = new ConfigUser();
            $configUser->finger_id = "generated_finger_id";
            $configUser->unid_activa = json_encode([0]);
            $configUser->save();
            $user->config_user_id = $configUser->id;
        }
        $configUser->menu_ids = json_encode($menusId);
        $configUser->evento_asignado = json_encode($validatedData['eventos_id'] ?? []);
        $configUser->oficina_asignada = json_encode($validatedData['oficinas_ids'] ?? []);
        $configUser->save();

        // Actualizar el usuario
        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];
        if (isset($validatedData['password'])) {
            $user->password = Hash::make($validatedData['password']);
        }
        $user->role_id = $validatedData['role_id'];
        $user->is_admin = $validatedData['role_id'] == 1;
        $user->cedula = $validatedData['cedula'];
        $user->save();

        // Verificar si existe una Persona con la misma cédula
        $persona = Persona::where('cedula', $validatedData['cedula'])->first();

        // Si existe, asignar el ID del usuario actualizado
        if ($persona) {
            $persona->user_id = $user->id;
            $persona->save();
        }

        // Respuesta JSON
        return response()->json(["message" => "Usuario actualizado"], 200);
    }
}