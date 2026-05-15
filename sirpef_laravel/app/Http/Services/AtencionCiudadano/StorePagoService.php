<?php

namespace App\Http\Services\AtencionCiudadano;

use App\Models\Pago;
use App\Models\Registro;
use App\Models\Proveedor;
use App\Models\Recaudo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StorePagoService
{
    /**
     * Registra un nuevo pago, gestiona proveedores y procesa archivos adjuntos.
     */
    static public function crearPago(Request $request, int $registroId): JsonResponse
    {
        return DB::transaction(function () use ($request, $registroId) {
            try {
                // 1. Verificar que el Registro (Caso) exista
                $registro = Registro::findOrFail($registroId);

                // 2. Crear el registro principal del Pago
                $pago = Pago::create([
                    'orden_pago'           => $request->orden_pago,
                    'fecha_orden_pago'     => $request->fecha_orden_pago,
                    'monto'                => $request->monto,
                    'descripcion'          => $request->descripcion,
                    'fecha_pago_financiero'=> $request->fecha_pago_financiero,
                    'saldo_deudor'         => $request->saldo_deudor ?? $request->monto,
                    'saldo_acreedor'       => $request->saldo_acreedor ?? 0,
                    'cuota_compromiso_disponible' => $request->cuota_compromiso ?? 0,
                    'estatus_pago_id'      => $request->estatus_pago_id,
                    'tipo_pago_id'         => $request->tipo_pago_id,
                    'registro_id'          => $registroId,
                ]);

                // 3. Procesar Proveedores
                if ($request->has('proveedores') && is_array($request->proveedores)) {
                    foreach ($request->proveedores as $item) {
                        if (empty($item['cedula_rif'])) continue;

                        $proveedor = Proveedor::updateOrCreate(
                            ['cedula_rif' => trim($item['cedula_rif'])],
                            ['nombre'     => $item['nombre'] ?? 'Proveedor Desconocido']
                        );

                        $pago->proveedores()->attach($proveedor->id, [
                            'monto_relacionado' => $item['monto'] ?? 0,
                            'created_at'        => now(),
                            'updated_at'        => now(),
                        ]);
                    }
                }

                // 4. Procesar Recaudos
                $recaudosSubidos = self::processRecaudos($request, $registroId, $pago->id);

                return response()->json([
                    'success' => true,
                    'message' => 'Pago y documentos registrados exitosamente.',
                    'data'    => [
                        'pago'      => $pago->load('proveedores', 'estatus', 'tipoPago', 'recaudos'),
                        'recaudos'  => $recaudosSubidos
                    ]
                ], 201);

            } catch (\Exception $e) {
                Log::error("Error crítico en StorePagoService: " . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo completar el registro del pago.',
                    'error'   => $e->getMessage()
                ], 500);
            }
        }); 
    }

    /**
     * Maneja la subida física de archivos.
     */
    private static function processRecaudos(Request $request, int $registroId, int $pagoId): array
    {
        $recaudosProcesados = [];

        // Validamos que el array de recaudos exista
        if ($request->has('recaudos') && is_array($request->recaudos)) {
            
            foreach ($request->recaudos as $index => $recaudoData) {
                
                // LA CLAVE: Laravel estructura los archivos en arrays así: recaudos.0.archivo
                if ($request->hasFile("recaudos.{$index}.archivo")) {
                    
                    $archivoObj = $request->file("recaudos.{$index}.archivo");
                    
                    // Guardar archivo
                    $path = $archivoObj->store('recaudos/pagos', 'public');

                    // Crear registro en DB
                    $recaudosProcesados[] = Recaudo::create([
                        'nombre'      => $recaudoData['nombre'] ?? 'Soporte de Pago',
                        'path'        => $path,
                        'registro_id' => $registroId,
                        'pago_id'     => $pagoId,
                        'mime_type'   => $archivoObj->getMimeType()
                    ]);
                }
            }
        }

        return $recaudosProcesados;
    }
}