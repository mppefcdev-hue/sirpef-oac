<?php

namespace App\Http\Services\Memorandum;

use App\Models\Memorandum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreMemorandumService
{
    public static function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            try {
                $memorandum = Memorandum::create([
                    'numero'       => $request->numero,
                    'fecha'        => $request->fecha,
                    'asunto'       => $request->asunto,
                    'cuerpo'       => $request->cuerpo,
                    'remitente'    => $request->remitente,
                    'destinatario' => $request->destinatario,
                    'registro_id'  => $request->registro_id,
                ]);

                return response()->json([
                    'message' => 'Memorandum guardado exitosamente',
                    'success' => true,
                    'data' => $memorandum
                ], 201);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Error al guardar el memorandum',
                    'success' => false,
                    'error' => $e->getMessage()
                ], 500);
            }
        });
    }
}
