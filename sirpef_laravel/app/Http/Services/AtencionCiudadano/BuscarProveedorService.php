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
        // Limpiamos el string por si viene con espacios
        $documento = trim($documento);

        // Buscamos en la tabla tbl_proveedor
        // Asumiendo que tu tabla tiene una columna 'cedula_rif' o similar.
        // Si el campo se llama distinto, ajústalo aquí.
        $proveedor = Proveedor::where('cedula_rif', $documento)
            ->orWhere('rif', $documento) // Por si tienes campos separados
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
                'cedula_rif' => $proveedor->cedula_rif ?? $proveedor->rif,
                // Agrega aquí otros campos que necesites autorrellenar
            ]
        ], 200);
    }
}