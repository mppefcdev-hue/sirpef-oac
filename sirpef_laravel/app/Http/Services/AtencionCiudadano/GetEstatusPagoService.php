<?php

namespace App\Http\Services\AtencionCiudadano;

use App\Models\EstatusPago;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class GetEstatusPagoService
{
    /**
     * Obtiene todos los estatus de pago registrados en la base de datos.
     */
    static public function getAllEstatusPago(): JsonResponse
    {
        try {
            $estatus = EstatusPago::select('id', 'nombre', 'color', 'descripcion')
                ->orderBy('nombre', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data'    => $estatus
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error al obtener estatus de pago: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'No se pudieron obtener los estatus de pago.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}