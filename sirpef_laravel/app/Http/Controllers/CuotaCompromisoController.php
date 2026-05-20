<?php

namespace App\Http\Controllers;

use App\Models\CuotaCompromiso;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CuotaCompromisoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (!in_array(Auth::user()->role_id, [1, 2])) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $cuotas = CuotaCompromiso::orderBy('year', 'desc')
            ->orderBy('mes', 'desc')
            ->get();

        $data = $cuotas->map(function ($cuota) {
            $montoEjecutado = DB::table('tbl_pagos')
                ->whereYear(DB::raw("COALESCE(fecha_orden_pago, fecha_pago_financiero, created_at)"), $cuota->year)
                ->whereMonth(DB::raw("COALESCE(fecha_orden_pago, fecha_pago_financiero, created_at)"), $cuota->mes)
                ->whereNull('deleted_at')
                ->sum('monto');

            return [
                'id' => $cuota->id,
                'ano' => $cuota->year,
                'mes' => $cuota->mes,
                'monto_limite' => floatval($cuota->monto),
                'monto_ejecutado' => floatval($montoEjecutado),
                'monto_disponible' => floatval($cuota->monto) - floatval($montoEjecutado),
                'created_at' => $cuota->created_at ? $cuota->created_at->toIso8601String() : null,
                'updated_at' => $cuota->updated_at ? $cuota->updated_at->toIso8601String() : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if (!in_array(Auth::user()->role_id, [1, 2])) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $request->validate([
            'ano' => 'required|integer|min:2020|max:2100',
            'mes' => 'required|integer|min:1|max:12',
            'monto' => 'required|numeric|min:0',
        ]);

        // Once a monthly quota is set, it cannot be modified via store
        $exists = CuotaCompromiso::where('year', $request->ano)
            ->where('mes', $request->mes)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe una cuota registrada para este mes y año. Llame a un administrador o director para modificarla.'
            ], 422);
        }

        $cuota = CuotaCompromiso::create([
            'year' => $request->ano,
            'mes' => $request->mes,
            'monto' => $request->monto
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cuota de compromiso guardada exitosamente.',
            'data' => $cuota
        ]);
    }

    /**
     * Update an existing monthly quota.
     */
    public function update(Request $request): JsonResponse
    {
        if (!in_array(Auth::user()->role_id, [1, 2])) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $request->validate([
            'ano' => 'required|integer|min:2020|max:2100',
            'mes' => 'required|integer|min:1|max:12',
            'monto' => 'required|numeric|min:0',
        ]);

        $cuota = CuotaCompromiso::where('year', $request->ano)
            ->where('mes', $request->mes)
            ->first();

        if (!$cuota) {
            return response()->json([
                'success' => false,
                'message' => 'No existe una cuota registrada para este mes y año. Primero debe guardarla.'
            ], 404);
        }

        $cuota->monto = $request->monto;
        $cuota->save();

        return response()->json([
            'success' => true,
            'message' => 'Cuota de compromiso modificada exitosamente.',
            'data' => $cuota
        ]);
    }

    public function destroy($id): JsonResponse
    {
        if (!in_array(Auth::user()->role_id, [1, 2])) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $cuota = CuotaCompromiso::findOrFail($id);
        $cuota->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cuota de compromiso eliminada exitosamente.'
        ]);
    }
}
