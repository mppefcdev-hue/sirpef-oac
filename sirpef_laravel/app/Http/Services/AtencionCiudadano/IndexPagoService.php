<?php

namespace App\Http\Services\AtencionCiudadano;

use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class IndexPagoService
{
    /**
     * Obtiene la lista de pagos con paginación y relaciones.
     */
    static public function index(Request $request): JsonResponse
    {
        try {
            // 1. Definir cantidad de elementos por página (por defecto 10)
            $perPage = $request->input('per_page', 10);

            // 2. Construir la consulta con Eager Loading para evitar el problema N+1
            $query = Pago::with(['proveedores', 'estatus', 'tipoPago', 'recaudos'])
                         ->orderBy('created_at', 'desc');

            // 3. Filtro opcional: Búsqueda por número de orden de pago
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where('orden_pago', 'LIKE', "%{$search}%")
                      ->orWhere('descripcion', 'LIKE', "%{$search}%");
            }

            // 4. Ejecutar paginación
            $pagos = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data'    => $pagos->items(), // Los registros de la página actual
                'meta'    => [
                    'current_page' => $pagos->currentPage(),
                    'last_page'    => $pagos->lastPage(),
                    'per_page'     => $pagos->perPage(),
                    'total'        => $pagos->total(),
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error en IndexPagoService: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'No se pudo obtener la lista de pagos.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}