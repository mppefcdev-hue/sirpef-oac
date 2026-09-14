<?php

namespace App\Http\Services\AtencionCiudadano;

use App\Models\PuntoCuenta;
use App\Models\Registro;
use Illuminate\Http\JsonResponse;

class BuscarRegistroPorPuntoService
{
    /**
     * Obtiene los datos del Registro y Punto de Cuenta.
     * Soporta formatos con slash como "454/2024".
     */
    static public function obtenerDataParaPago(string $numeroPunto): JsonResponse
    {
        // Limpiamos y decodificamos por si el slash viene como %2F
        $numeroPuntoLimpio = urldecode(trim($numeroPunto));

        // 1. Buscar el Punto de Cuenta por número de punto o por ID
        $puntoCuenta = PuntoCuenta::where('numero_punto', $numeroPuntoLimpio)
            ->orWhere('id', $numeroPuntoLimpio)
            ->first();

        // 2. Si no se encontró y el parámetro es numérico, intentar buscar directamente por registro_id
        $registro = null;
        if ($puntoCuenta) {
            $registro = Registro::where('punto_cuenta_id', $puntoCuenta->id)->first();
        } elseif (is_numeric($numeroPuntoLimpio)) {
            $registro = Registro::find($numeroPuntoLimpio);
            if ($registro && $registro->punto_cuenta_id) {
                $puntoCuenta = PuntoCuenta::find($registro->punto_cuenta_id);
            }
        }

        if (!$puntoCuenta) {
            return response()->json([
                'success' => false,
                'message' => "No existe el Punto de Cuenta: {$numeroPuntoLimpio}"
            ], 404);
        }

        // 2. Buscar el Registro asociado con sus relaciones
        $registro = Registro::with(['pago.estatus', 'pago.proveedores', 'eventoPersona.persona'])->where('punto_cuenta_id', $puntoCuenta->id)->first();

        if (!$registro) {
            return response()->json([
                'success' => false,
                'message' => "El Punto de Cuenta existe, pero no tiene un Registro (Caso) asociado."
            ], 404);
        }

        $pago = $registro->pago ?? null;
        $proveedor = ($pago && $pago->proveedores->isNotEmpty()) ? $pago->proveedores->first() : null;

        // 3. Retornar la data completa para el formulario
        return response()->json([
            'success' => true,
            'data' => [
                'punto_cuenta_id'       => $puntoCuenta->id,
                'numero_punto'          => $puntoCuenta->numero_punto,
                'fecha_punto'           => $puntoCuenta->fecha ? $puntoCuenta->fecha->format('d/m/Y') : null,
                'registro_id'           => $registro->id,
                'monto'                 => $pago->monto ?? null,
                'nro_orden_pago'        => $pago->orden_pago ?? null,
                'orden_pago'            => $pago->orden_pago ?? null,
                'fecha_orden_pago'      => $pago->fecha_orden_pago ?? null,
                'nro_factura'           => $pago->nro_factura ?? null,
                'estatus_pago_id'       => $pago->estatus_pago_id ?? null,
                'tipo_pago_id'          => $pago->tipo_pago_id ?? null,
                'descripcion'           => $pago->descripcion ?? $registro->descripcion ?? null,
                'beneficiario'          => $pago->beneficiario ?? $registro->eventoPersona?->persona?->nombre_completo ?? null,
                'diagnostico'           => $pago->diagnostico ?? null,
                'proveedor'             => $proveedor?->nombre ?? null,
                'rif_proveedor'         => $proveedor?->cedula_rif ?? null,
                'saldo_deudor'          => $pago->saldo_deudor ?? null,
                'saldo_acreedor'        => $pago->saldo_acreedor ?? null,
                'fecha_pago_financiero' => $pago->fecha_pago_financiero ?? null,
            ]
        ], 200);
    }
}