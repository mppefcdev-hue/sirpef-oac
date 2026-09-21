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

                $monto = floatval($request->monto);
                $saldoAcreedor = floatval($request->saldo_acreedor ?? 0);
                $saldoDeudor = $request->has('saldo_deudor') && $request->saldo_deudor !== null
                    ? floatval($request->saldo_deudor)
                    : max(0, $monto - $saldoAcreedor);

                $descripcion = $request->descripcion;
                if (!empty($request->nro_factura) && stripos($descripcion ?? '', '[Factura:') === false) {
                    $descripcion = trim(($descripcion ?? '') . " [Factura: " . trim($request->nro_factura) . "]");
                }

                // 2. Crear el registro principal del Pago
                $pago = Pago::create([
                    'orden_pago'           => $request->orden_pago,
                    'fecha_orden_pago'     => $request->fecha_orden_pago,
                    'monto'                => $monto,
                    'descripcion'          => $descripcion,
                    'fecha_pago_financiero'=> $request->fecha_pago_financiero,
                    'saldo_deudor'         => $saldoDeudor,
                    'saldo_acreedor'       => $saldoAcreedor,
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

    /**
     * Obtiene un pago específico con sus relaciones.
     */
    public static function obtenerPago(int $id): JsonResponse
    {
        try {
            $pago = Pago::with([
                'proveedores',
                'estatus',
                'tipoPago',
                'recaudos',
                'registro.puntoCuenta.memorandum',
                'registro.eventoPersona.persona',
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data'    => $pago
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró el pago solicitado.',
                'error'   => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Actualiza un pago existente y sus relaciones.
     */
    public static function actualizarPago(Request $request, int $id): JsonResponse
    {
        return DB::transaction(function () use ($request, $id) {
            try {
                $pago = Pago::findOrFail($id);

                $monto = floatval($request->monto ?? $pago->monto);
                $saldoAcreedor = floatval($request->saldo_acreedor ?? $pago->saldo_acreedor ?? 0);
                $saldoDeudor = $monto - $saldoAcreedor;

                $descripcion = $request->descripcion ?? $pago->descripcion;
                if (!empty($request->nro_factura) && stripos($descripcion ?? '', '[Factura:') === false) {
                    $descripcion = trim(($descripcion ?? '') . " [Factura: " . trim($request->nro_factura) . "]");
                }

                $pago->update([
                    'orden_pago'           => $request->orden_pago ?? $pago->orden_pago,
                    'fecha_orden_pago'     => $request->fecha_orden_pago ?? $pago->fecha_orden_pago,
                    'monto'                => $monto,
                    'descripcion'          => $descripcion,
                    'fecha_pago_financiero'=> $request->fecha_pago_financiero ?? $pago->fecha_pago_financiero,
                    'saldo_deudor'         => $saldoDeudor,
                    'saldo_acreedor'       => $saldoAcreedor,
                    'cuota_compromiso_disponible' => $request->cuota_compromiso ?? $pago->cuota_compromiso_disponible,
                    'estatus_pago_id'      => $request->estatus_pago_id ?? $pago->estatus_pago_id,
                    'tipo_pago_id'         => $request->tipo_pago_id ?? $pago->tipo_pago_id,
                ]);

                // Actualizar proveedores si vienen en la petición
                if ($request->has('proveedores') && is_array($request->proveedores)) {
                    $pago->proveedores()->detach();
                    foreach ($request->proveedores as $item) {
                        if (empty($item['cedula_rif'])) continue;

                        $proveedor = Proveedor::updateOrCreate(
                            ['cedula_rif' => trim($item['cedula_rif'])],
                            ['nombre'     => $item['nombre'] ?? 'Proveedor Desconocido']
                        );

                        $pago->proveedores()->attach($proveedor->id, [
                            'monto_relacionado' => $item['monto_relacionado'] ?? $item['monto'] ?? $monto,
                            'created_at'        => now(),
                            'updated_at'        => now(),
                        ]);
                    }
                }

                // Subir nuevos recaudos si se adjuntaron
                $recaudosSubidos = self::processRecaudos($request, $pago->registro_id, $pago->id);

                return response()->json([
                    'success' => true,
                    'message' => 'Pago actualizado exitosamente.',
                    'data'    => [
                        'pago'      => $pago->fresh()->load('proveedores', 'estatus', 'tipoPago', 'recaudos'),
                        'recaudos'  => $recaudosSubidos
                    ]
                ], 200);

            } catch (\Exception $e) {
                Log::error("Error crítico actualizando pago en StorePagoService: " . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo actualizar el pago.',
                    'error'   => $e->getMessage()
                ], 500);
            }
        });
    }
}