<?php

namespace App\Http\Services\AtencionCiudadano;

use App\Models\Proveedor;
use Illuminate\Http\JsonResponse;

class BuscarProveedorService
{
    /**
     * Busca un proveedor por su documento de identidad (Cédula o RIF).
     */
    static public function buscarPorDocumento(string $documento): JsonResponse
    {
        // Normalizamos el documento (sin espacios y en mayúsculas)
        $documento = strtoupper(preg_replace('/\s+/', '', trim($documento)) ?? '');
        $documentoSinSeparadores = preg_replace('/[^A-Z0-9]/', '', $documento) ?? '';

        // Buscamos en la tabla tbl_proveedor por su RIF.
        // Se comparan ambos formatos: exacto y sin guiones/espacios/puntos.
        $proveedor = Proveedor::where('cedula_rif', $documento)
            ->orWhereRaw(
                "UPPER(REPLACE(REPLACE(REPLACE(cedula_rif, '-', ''), ' ', ''), '.', '')) = ?",
                [$documentoSinSeparadores]
            )
            ->first();

        if (!$proveedor) {
            return response()->json([
                'success' => false,
                'exists'  => false,
                'message' => 'Proveedor no encontrado en la base de datos.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'exists'  => true,
            'data'    => [
                'id'         => $proveedor->id,
                'nombre'     => $proveedor->nombre,
                'cedula_rif' => $proveedor->cedula_rif,
                // Agrega aquí otros campos que necesites autorrellenar
            ]
        ], 200);
    }
}