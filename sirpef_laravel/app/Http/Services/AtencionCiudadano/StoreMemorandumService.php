<?php

namespace App\Http\Services\AtencionCiudadano;

use App\Models\Memorandum;
use App\Models\PuntoCuenta;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class StoreMemorandumService
{
    /**
     * Busca un Punto de Cuenta por su número y devuelve los datos de la persona asociada.
     *
     * @param string $numero
     * @return JsonResponse
     */
    public static function buscarPuntoCuenta(string $numero): JsonResponse
    {
        try {
            $puntoCuenta = PuntoCuenta::where('numero_punto', 'LIKE', trim($numero))
                ->with(['registros.eventoPersona.persona'])
                ->first();

            if (!$puntoCuenta) {
                return response()->json([
                    'message' => 'Punto de Cuenta no encontrado',
                    'success' => false
                ], 404);
            }

            // Intentamos obtener la persona desde el primer registro asociado
            $registro = $puntoCuenta->registros->first();
            $persona = $registro ? $registro->eventoPersona->persona : null;

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $puntoCuenta->id,
                    'numero_punto' => $puntoCuenta->numero_punto,
                    'solicitante' => $persona ? $persona->nombre_completo : 'No asignado',
                    'solicitante_id' => $persona ? $persona->id : null,
                    'cedula' => $persona ? $persona->cedula : 'N/A',
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al buscar el Punto de Cuenta',
                'error' => $e->getMessage(),
                'success' => false
            ], 500);
        }
    }

    /**
     * Store a new memorandum record.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public static function store(Request $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            try {
                $user = auth()->user();
                if (!$user) {
                    return response()->json(['message' => 'No autorizado', 'success' => false], 401);
                }

                $validated = $request->validate([
                    'numero_punto_cuenta' => 'required|string|exists:tbl_punto_cuenta,numero_punto',
                    'codigo'              => 'required|string',
                    'de'                  => 'required|string',
                    'para'                => 'required|string',
                    'asunto'              => 'required|string',
                    'fecha'               => 'required|date',
                    'cuerpo'              => 'required|string',
                ]);

                // Buscar el ID del punto de cuenta basándose en el número ingresado
                $puntoCuenta = PuntoCuenta::where('numero_punto', $validated['numero_punto_cuenta'])->firstOrFail();

                $memorandum = Memorandum::create([
                    'punto_cuenta_id' => $puntoCuenta->id,
                    'codigo'          => $validated['codigo'],
                    'de'              => $validated['de'],
                    'para'            => $validated['para'],
                    'asunto'          => $validated['asunto'],
                    'fecha'           => $validated['fecha'],
                    'cuerpo'          => $validated['cuerpo'],
                ]);

                // Auditoría
                $auditoria = new Auditoria();
                $auditoria->user_id = $user->id;
                $auditoria->evento_id = $user->configUser->evento_activo ?? null;
                $auditoria->descripcion = "El usuario {$user->name} creó el memorándum {$memorandum->codigo} asociado al Punto de Cuenta N° {$puntoCuenta->numero_punto}.";
                $auditoria->save();

                return response()->json([
                    'message' => 'Memorándum guardado exitosamente',
                    'success' => true,
                    'data'    => $memorandum
                ], 201);

            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Error al guardar el memorándum: ' . $e->getMessage(),
                    'success' => false
                ], 500);
            }
        });
    }
}
