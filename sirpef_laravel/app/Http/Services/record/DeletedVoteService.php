<?php

namespace App\Http\Services\record;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Persona;
use App\Models\Registro;
use App\Models\UnidadAdscrita;
use App\Models\Auditoria;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class DeletedVoteService
{

    /**
     * "Elimina" lógicamente (soft delete) un registro específico por su ID.
     * También registra una entrada de auditoría.
     *
     * @param int $id El ID del registro que debe ser eliminado lógicamente.
     * @return JsonResponse
     */
    static public function DeleteVote($id)
    {
        try {
            // 1. Buscar el registro específico por su ID
            $registro = Registro::find($id);

            if (!$registro) {
                return response()->json(['msg' => 'Registro no encontrado', 'success' => false], 404);
            }

            // 2. Obtener la persona asociada a este registro para el log de auditoría
            $persona = $registro->eventoPersona->persona ?? null;

            if (!$persona) {
                $personaName = 'Persona Desconocida (ID de registro: ' . $id . ')';
                $personaIdForAudit = null;
            } else {
                $personaName = $persona->nombre_completo;
                $personaIdForAudit = $persona->id;
            }

            // 3. Realizar el borrado lógico del registro específico
            // ¡Esta línea es la clave para el soft delete, y ya está correcta!
            $registro->delete(); 

            // 4. Obtener información del usuario para la auditoría
            $user = Auth::user();
            $unidadActiva = 'Administrador';

            /*if ($user && $user->configUser) {
                if ($user->isAdmin() || !empty($user->configUser->unid_activa)) {
                    $unidActivaIds = json_decode($user->configUser->unid_activa, true);
                    
                    if (is_array($unidActivaIds) && !in_array(0, $unidActivaIds) && !empty($unidActivaIds[0])) {
                        $unidadAdscrita = UnidadAdscrita::find($unidActivaIds[0]);
                        if ($unidadAdscrita) {
                            $unidadActiva = $unidadAdscrita->nombre;
                        }
                    }
                }
            }*/

            // 5. Crear mensaje de auditoría
            $message = "El usuario {$user->name} de la unidad adscrita {$unidadActiva} realizó una eliminación lógica del registro (ID: {$id}) de {$personaName}";
            
            $auditoria = new Auditoria();
            $auditoria->descripcion = $message;
            $auditoria->user_id = $user->id;
            $auditoria->persona_id = $personaIdForAudit;
            $auditoria->save();
    
            // ¡Aquí es donde cambiamos el mensaje de éxito!
            return response()->json(['msg' => "Eliminado Correctamente", 'success' => true], 200);

        } catch (\Throwable $th) {
            Log::error("Error al realizar soft delete en DeleteVoteService: " . $th->getMessage() . " - " . $th->getTraceAsString());
            return response()->json(['msg' => 'Error al eliminar el registro: ' . $th->getMessage(), 'success' => false], 500);
        }
    }
}