<?php

namespace App\Http\Services\AtencionCiudadano;

use Illuminate\Http\{
    Request,
    JsonResponse
};
use App\Models\Registro;
use App\Models\Persona; // Ya está aquí, pero es bueno recordarlo
use App\Models\Recaudo; // Ya está aquí
use Illuminate\Support\Facades\DB; // Se mantiene si se usa en otros métodos o si se permite aquí por contexto
use Illuminate\Validation\ValidationException; // Se mantiene

class ShowCasosService {

    /**
     * Obtiene un registro específico con toda su información relacionada,
     * incluyendo el estado y municipio de la parroquia de la persona.
     *
     * @param int $id ID del registro
     * @return JsonResponse
     */
    public static function show(int $id): JsonResponse
    {
        try {
            // Obtener el registro con todas las relaciones necesarias,
            // ¡Incluyendo la cadena de relaciones para parroquia, municipio y estado!
            $registro = Registro::with([
                'eventoPersona.persona.parroquia.municipio.estado',
                'tipoCaso',
                'puntoCuenta.memorandum',
                'proveedores',
                'recaudos',
            ])->findOrFail($id);

            // Obtener persona relacionada
            $persona = $registro->eventoPersona->persona ?? null;
            $parroquia = $persona->parroquia ?? null;
            $municipio = $parroquia->municipio ?? null;
            $estado = $municipio->estado ?? null;

            // Construir la respuesta
            $response = [
                'registro_id' => $registro->id,
                'voto' => $registro->voto,
                'descripcion' => $registro->descripcion,
                'hora_voto' => $registro->hora_voto,
                'observacion' => $registro->observacion,
                'referencia' => $registro->referencia,
                'fecha_registro' => $registro->created_at->format('d/m/Y H:i:s'),
                'estatus_caso' => $registro->estatus_caso, // Mueve aquí para mejor organización

                // Información de tipo de caso
                'tipo_caso' => [ // Agrupa la información del tipo de caso
                    'id' => $registro->tipoCaso->id ?? null, // Usar null en lugar de 'Sin tipo' si es un ID
                    'tipo' => $registro->tipoCaso->tipo ?? 'Sin tipo',
                ],

                // Información de persona
                'persona' => [
                    'id' => $persona->id ?? null,
                    'nombre_completo' => $persona->nombre_completo ?? 'No disponible',
                    'ministerio_id' => $persona->ministerio_id ?? 'No disponible',
                    'genero' => self::formatearGenero($persona->sexo ?? null),
                    'cedula' => $persona->cedula ?? 'No disponible',
                    'fecha_nacimiento' => $persona->fecha_nacimiento ?? 'No disponible',
                    'telefono' => $persona->telefono ?? 'No disponible',
                    'sexo' => $persona->sexo ?? 'No disponible',
                    'direccion' => $persona->direccion ?? 'No disponible',
                    'parroquia' => [
                        'id' => $parroquia->id ?? null,
                        'nombre' => $parroquia->parroquias ?? 'No disponible',
                    ],
                    'municipio' => [
                        'id' => $municipio->id ?? null,
                        'nombre' => $municipio->municipio ?? 'No disponible',
                    ],
                    'estado' => [
                        'id' => $estado->id ?? null,
                        'nombre' => $estado->estado ?? 'No disponible',
                    ],
                ],

                // Información de punto de cuenta
                'punto_cuenta' => $registro->puntoCuenta ? [
                    'id' => $registro->puntoCuenta->id,
                    'numero_punto' => $registro->puntoCuenta->numero_punto,
                    'asunto' => $registro->puntoCuenta->asunto,
                    'decision' => $registro->puntoCuenta->decision,
                    'estatus' => $registro->puntoCuenta->estatus_pc,
                    'memorandum' => $registro->puntoCuenta->memorandum ? [
                        'id' => $registro->puntoCuenta->memorandum->id,
                        'codigo' => $registro->puntoCuenta->memorandum->codigo,
                    ] : null,
                ] : null,

                // Lista de proveedores (usando la relación definida)
                'proveedores' => $registro->proveedores->map(function($proveedor) {
                    return [
                        'nombre' => $proveedor->nombre,
                        'monto' => (float)$proveedor->monto
                    ];
                })->toArray(),

                // Lista de recaudos
                'recaudos' => $registro->recaudos->map(function($recaudo) {
                    return [
                        'id' => $recaudo->id,
                        'nombre' => $recaudo->nombre,
                        'path' => $recaudo->path,
                        'mime_type' => $recaudo->mime_type,
                    ];
                })->toArray()
            ];

            return response()->json([
                'success' => true,
                'data' => $response
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registro no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el registro',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Formatea el género para una mejor presentación
     */
    private static function formatearGenero(?string $genero): string
    {
        return match ($genero) {
            'M' => 'Masculino',
            'F' => 'Femenino',
            default => 'No especificado'
        };
    }
}
