<?php

namespace App\Http\Services\Memos;

use App\Models\Proveedor;
use Illuminate\Http\JsonResponse;

class BuscarProveedoresService
{
    /**
     * Busca proveedores por coincidencia parcial de RIF (cédula_rif).
     *
     * @param string $termino
     * @return JsonResponse
     */
    public static function buscar(string $termino): JsonResponse
    {
        $termino = strtoupper(preg_replace('/\s+/', '', trim($termino)) ?? '');
        $terminoSinSeparadores = preg_replace('/[^A-Z0-9]/', '', $termino) ?? '';

        if ($terminoSinSeparadores === '') {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $like = '%' . self::escapeLike($termino) . '%';
        $likeSimple = '%' . self::escapeLike($terminoSinSeparadores) . '%';

        $proveedores = Proveedor::query()
            ->whereNotNull('cedula_rif')
            ->where(function ($query) use ($like, $likeSimple) {
                $query->where('cedula_rif', 'LIKE', $like)
                    ->orWhereRaw(
                        "UPPER(REPLACE(REPLACE(REPLACE(cedula_rif, '-', ''), ' ', ''), '.', '')) LIKE ?",
                        [$likeSimple]
                    );
            })
            ->orderBy('nombre')
            ->limit(10)
            ->get(['id', 'nombre', 'cedula_rif', 'telefono']);

        return response()->json([
            'success' => true,
            'data' => $proveedores,
        ]);
    }

    private static function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
