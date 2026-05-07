<?php

namespace App\Http\Services\AtencionCiudadano;

use Illuminate\Http\{
    Request,
    JsonResponse
};
use App\Models\PuntoCuenta;
use App\Models\Registro;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdatePuntoCuentaService {

    /**
     * Edita un punto de cuenta existente.
     * Después de cada edición, establece 'estatus_pc' a true automáticamente
     * y incrementa 'cantidad_editada' en 1 automáticamente.
     *
     * @param Request $request
     * @param int $puntoCuentaId El ID del PuntoCuenta a editar.
     * @return JsonResponse
     */
    public static function updatePuntoCuenta(Request $request, int $puntoCuentaId): JsonResponse
    {
        return DB::transaction(function () use ($request, $puntoCuentaId) {
            try {
                $puntoCuenta = PuntoCuenta::findOrFail($puntoCuentaId);

                $validatedData = $request->validate([
                    'presentado_a' => 'sometimes|required|string|max:255',
                    'presentado_por' => 'sometimes|required|string|max:255',
                    'fecha' => 'sometimes|required|date',
                    'numero_punto' => 'sometimes|required|string|max:50',
                    'asunto' => 'sometimes|required|string',
                    'exposicion_motivos' => 'sometimes|required|string',
                    'propuesta' => 'sometimes|required|string',
                    'decision' => 'nullable|string|in:APROBADO,NEGADO,VISTO,DIFERIDO,OTRO',
                    'otras_instrucciones' => 'nullable|string',
                    'anexos' => 'nullable|boolean',
                    'cargo_a' => 'nullable|string|max:255',
                    'cargo_por' => 'nullable|string|max:255',
                    'resolucion_1' => 'nullable|string',
                    'resolucion_2' => 'nullable|string',
                    'proveedores' => 'nullable|array',
                    'proveedores.*.nombre' => 'required_with:proveedores|string|max:255',
                    'proveedores.*.monto' => 'required_with:proveedores|numeric',
                ]);

                $validatedData['estatus_pc'] = true;
                $puntoCuenta->increment('cantidad_editada');

                $puntoCuenta->fill($validatedData);
                $puntoCuenta->save();

                if ($request->has('proveedores')) {
                    // Assuming a relationship exists or syncing via the Registro associated
                    $registro = $puntoCuenta->registro; 
                    if ($registro) {
                        $registro->proveedores()->delete();
                        foreach ($request->proveedores as $prov) {
                            $registro->proveedores()->create([
                                'nombre' => $prov['nombre'],
                                'monto' => $prov['monto']
                            ]);
                        }
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Punto de cuenta actualizado exitosamente. Estatus configurado a activo y contador de edición incrementado.',
                    'data' => $puntoCuenta
                ], 200);

            } catch (ValidationException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $e->errors()
                ], 422);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Punto de cuenta no encontrado'
                ], 404);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar el punto de cuenta',
                    'error' => $e->getMessage()
                ], 500);
            }
        });
    }
}
