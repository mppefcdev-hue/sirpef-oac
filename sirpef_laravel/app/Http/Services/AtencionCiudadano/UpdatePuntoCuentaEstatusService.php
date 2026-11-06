<?php

namespace App\Http\Services\AtencionCiudadano;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request; // Still needed for context in other services, but not directly used in this method for $request->input()
use App\Models\PuntoCuenta;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException; // Keeping for potential future specific validation needs
use Illuminate\Support\Facades\Auth; // Import Auth Facade
use Illuminate\Support\Facades\Log; // For logging errors

class UpdatePuntoCuentaEstatusService
{
    /**
     * Permite a un usuario administrador cambiar el estatus booleano de un Punto de Cuenta (true/false).
     *
     * @param int $puntoCuentaId El ID del PuntoCuenta a actualizar.
     * @return JsonResponse
     * @throws \Exception Si el usuario no es administrador o si ocurre un error inesperado.
     */
    public static function EstatusPuntoCuenta(int $puntoCuentaId): JsonResponse
    {
        return DB::transaction(function () use ($puntoCuentaId) {
            try {
                // 1. Verificar si el usuario autenticado es administrador
                $user = Auth::user();
                if (!$user || !$user->isAdmin()) {
                    Log::warning("Intento de cambiar estatus de PuntoCuenta sin permisos de administrador. User ID: " . ($user ? $user->id : 'N/A') . ".");
                    throw new \Exception('Acceso denegado. Solo los administradores pueden cambiar el estatus del punto de cuenta.', 403); // 403 Forbidden
                }

                if ($user->persona && (int) $user->persona->ministerio_id === 19) {
                    Log::warning("Intento de cambiar estatus de PuntoCuenta por usuario de ministerio restringido (19). User ID: " . $user->id);
                    throw new \Exception('Acceso denegado. Los usuarios de este ministerio no tienen autorización para modificar el estatus del punto de cuenta.', 403);
                }

                // 2. Encontrar el Punto de Cuenta
                $puntoCuenta = PuntoCuenta::findOrFail($puntoCuentaId);

                // 3. Cambiar el estatus booleano (true a false, false a true)
                // Assuming 'estatus_pc' is stored as a boolean (0 or 1 in DB)
                $puntoCuenta->estatus_pc = !$puntoCuenta->estatus_pc; // Toggles the boolean value
                $puntoCuenta->save();

                // Convertir el booleano a una cadena amigable para el mensaje
                $newStatusText = $puntoCuenta->estatus_pc ? 'true' : 'false';

                return response()->json([
                    'success' => true,
                    'message' => "Estatus del punto de cuenta actualizado a '{$newStatusText}' exitosamente.",
                    'data' => $puntoCuenta
                ], 200);

            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                Log::error("PuntoCuenta no encontrado para ID: {$puntoCuentaId} al intentar cambiar estatus.");
                return response()->json([
                    'success' => false,
                    'message' => 'Punto de cuenta no encontrado'
                ], 404);
            } catch (\Exception $e) {
                // Captura la excepción de acceso denegado o cualquier otro error general
                Log::error("Error al cambiar estatus de PuntoCuenta ID: {$puntoCuentaId}. Mensaje: " . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], $e->getCode() ?: 500);
            }
        });
    }
}