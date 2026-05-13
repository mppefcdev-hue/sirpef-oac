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
        $pagoProveedor = PagoProveedor::find($id);

        if (!$pagoProveedor) {
            return response()->json([
                'success' => false,
                'msg' => 'Registro no encontrado'
            ], 404);
        }

        $pagoProveedor->delete();

        return response()->json([
            'success' => true,
            'msg' => 'Registro eliminado correctamente'
        ]);
    }
}
