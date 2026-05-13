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
        // El ID recibido corresponde al ID de la tabla tbl_pagos
        $pagoProveedor = PagoProveedor::where('pago_id', $id)->first();

        if (!$pagoProveedor) {
            return response()->json([
                'success' => false,
                'msg' => 'Relación de pago no encontrada'
            ], 404);
        }

        $pago = $pagoProveedor->pago;

        // Soft delete the pivot record
        $pagoProveedor->delete();

        // Soft delete the main payment record
        if ($pago) {
            $pago->delete();
        }

        return response()->json([
            'success' => true,
            'msg' => 'Registro eliminado correctamente'
        ]);
    }
}
