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
                $data = $request->all();

                if (empty($data['codigo'])) {
                    // Generar el código autoincrementable (Ej: 001/2024) si no viene en el request
                    $lastMemo = Memorandum::whereYear('created_at', date('Y'))->orderBy('id', 'desc')->first();
                    $lastNumber = 0;
                    if ($lastMemo && $lastMemo->codigo) {
                        $parts = explode('/', $lastMemo->codigo);
                        $lastNumber = isset($parts[0]) ? (int) $parts[0] : 0;
                    }
                    $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
                    $data['codigo'] = $newNumber . '/' . date('Y');
                }

                $memorandum = Memorandum::create($data);

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
