<?php

namespace App\Http\Services\AtencionCiudadano;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\PuntoCuenta;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GetPuntoCuentaService {

    /**
     * Obtiene los detalles de un Punto de Cuenta específico por su ID.
     *
     * @param int $puntoCuentaId El ID del Punto de Cuenta a buscar.
     * @return JsonResponse
     */
    public static function getPuntoCuenta(int $puntoCuentaId): JsonResponse
    {
        try {
            // Busca el Punto de Cuenta por su ID.
            // Si no se encuentra, lanzará una ModelNotFoundException.
            $puntoCuenta = PuntoCuenta::findOrFail($puntoCuentaId);

            // Retorna los datos del Punto de Cuenta.
            // Puedes personalizar la estructura de la respuesta si es necesario,
            // pero por defecto, toArray() incluirá todos los atributos $fillable
            // y cualquier otro atributo público o accesor definido.
            return response()->json([
                'success' => true,
                'data' => [
                    'id'                  => $puntoCuenta->id,
                    'presentado_a'        => $puntoCuenta->presentado_a,
                    'estatus' => $puntoCuenta->estatus_pc,
                    'presentado_por'      => $puntoCuenta->presentado_por,
                    'fecha'               => $puntoCuenta->fecha->format('Y-m-d'),
                    'numero_punto'        => $puntoCuenta->numero_punto,
                    'asunto'              => $puntoCuenta->asunto,
                    'exposicion_motivos'  => $puntoCuenta->exposicion_motivos,
                    'propuesta'           => $puntoCuenta->propuesta,
                    'decision'            => $puntoCuenta->decision,
                    'otras_instrucciones' => $puntoCuenta->otras_instrucciones,
                    'anexos'              => (bool) $puntoCuenta->anexos, // Asegura que sea booleano
                    'cargo_a'             => $puntoCuenta->cargo_a,
                    'cargo_por'           => $puntoCuenta->cargo_por,
                    'resolucion_1'        => $puntoCuenta->resolucion_1,
                    'resolucion_2'        => $puntoCuenta->resolucion_2,
                    'created_at'          => $puntoCuenta->created_at->format('d/m/Y H:i:s'),
                    'updated_at'          => $puntoCuenta->updated_at->format('d/m/Y H:i:s'),
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Manejo cuando el Punto de Cuenta no se encuentra
            return response()->json([
                'success' => false,
                'message' => 'Punto de Cuenta no encontrado.'
            ], 404);
        } catch (\Exception $e) {
            // Manejo de cualquier otro error inesperado
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el Punto de Cuenta.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
