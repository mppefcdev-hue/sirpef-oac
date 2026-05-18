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

        // 1. Buscar el Punto de Cuenta
        $puntoCuenta = PuntoCuenta::where('numero_punto', $numeroPuntoLimpio)->first();

        if (!$puntoCuenta) {
            return response()->json([
                'success' => false,
                'message' => "No existe el Punto de Cuenta: {$numeroPuntoLimpio}"
            ], 404);
        }

        // 2. Buscar el Registro asociado
        $registro = Registro::where('punto_cuenta_id', $puntoCuenta->id)->first();

        if (!$registro) {
            return response()->json([
                'success' => false,
                'message' => "El Punto de Cuenta existe, pero no tiene un Registro (Caso) asociado."
            ], 404);
        }

        // 3. Retornar la data completa para el formulario
        return response()->json([
            'success' => true,
            'data' => [
                'punto_cuenta_id' => $puntoCuenta->id,
                'numero_punto'    => $puntoCuenta->numero_punto, // <--- Agregado
                'fecha_punto'     => $puntoCuenta->fecha ? $puntoCuenta->fecha->format('d/m/Y') : null, // <--- Agregado
                'registro_id'     => $registro->id,

            ]
        ], 200);
    }
}