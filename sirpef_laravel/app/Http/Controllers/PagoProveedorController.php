<?php

namespace App\Http\Controllers;

use App\Models\PagoProveedor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PagoProveedorController extends Controller
{
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        // Intentamos buscar por ID de la tabla pivot o por pago_id
        $pagoProveedor = PagoProveedor::where('id', $id)
            ->orWhere('pago_id', $id)
            ->first();

        if (!$pagoProveedor) {
            return response()->json([
                'success' => false,
                'msg' => 'Registro no encontrado'
            ], 404);
        }

        $pago = $pagoProveedor->pago;
        $pagoProveedor->delete();

        if ($pago) {
            $pago->delete();
        }

        return response()->json([
            'success' => true,
            'msg' => 'Registro eliminado correctamente'
        ]);
    }
}
