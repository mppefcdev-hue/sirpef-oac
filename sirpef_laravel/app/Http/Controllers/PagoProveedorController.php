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
        // Buscamos la relación en la tabla pivot usando el ID de tbl_pagos
        $pagoProveedor = PagoProveedor::where('pago_id', $id)->first();

        // Si existe la relación, eliminamos ambos
        if ($pagoProveedor) {
            $pago = $pagoProveedor->pago;
            $pagoProveedor->delete();
            if ($pago) {
                $pago->delete();
            }
        } else {
            // Si no existe la relación en la pivot, intentamos eliminar el pago directamente
            $pago = \App\Models\Pago::find($id);
            if ($pago) {
                $pago->delete();
            } else {
                return response()->json([
                    'success' => false,
                    'msg' => 'Registro no encontrado'
                ], 404);
            }
        }

        return response()->json([
            'success' => true,
            'msg' => 'Registro eliminado correctamente'
        ]);
    }
}
