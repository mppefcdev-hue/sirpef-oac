<?php

namespace App\Http\Services\AtencionCiudadano;

use App\Models\PuntoCuenta;
use Illuminate\Http\JsonResponse;

class GetPuntoCuentaByNumeroService {

    /**
     * Obtiene los detalles de un Punto de Cuenta específico por su Número.
     *
     * @param string $numero El número del Punto de Cuenta a buscar.
     * @return JsonResponse
     */
    public static function getByNumero(string $numero): JsonResponse
    {
        // El número puede contener '/' que llega como parte del path gracias al regex en la ruta
        $numeroDecoded = urldecode($numero);
        
        $puntoCuenta = PuntoCuenta::where('numero_punto', $numeroDecoded)->first();

        if (!$puntoCuenta) {
            return response()->json([
                'success' => false,
                'message' => 'Punto de Cuenta no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id'                  => $puntoCuenta->id,
                'presentado_a'        => $puntoCuenta->presentado_a,
                'presentado_por'      => $puntoCuenta->presentado_por,
                'fecha'               => $puntoCuenta->fecha->format('Y-m-d'),
                'numero_punto'        => $puntoCuenta->numero_punto,
                'asunto'              => $puntoCuenta->asunto,
                'exposicion_motivos'  => $puntoCuenta->exposicion_motivos,
                'propuesta'           => $puntoCuenta->propuesta,
                'decision'            => $puntoCuenta->decision,
                'otras_instrucciones' => $puntoCuenta->otras_instrucciones,
                'anexos'              => (bool) $puntoCuenta->anexos,
                'cargo_a'             => $puntoCuenta->cargo_a,
                'cargo_por'           => $puntoCuenta->cargo_por,
                'resolucion_1'        => $puntoCuenta->resolucion_1,
                'resolucion_2'        => $puntoCuenta->resolucion_2,
            ]
        ], 200);
    }
}
