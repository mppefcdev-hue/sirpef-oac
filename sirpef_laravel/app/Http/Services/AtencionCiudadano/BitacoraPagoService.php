<?php

namespace App\Http\Services\AtencionCiudadano;

use App\Models\BitacoraPago;
use App\Models\Pago;
use App\Models\Registro;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class BitacoraPagoService
{
    /**
     * Obtiene todas las notas de bitácora para un pago dado.
     */
    public static function listarPorPago(int $pagoId): JsonResponse
    {
        try {
            $pago = Pago::findOrFail($pagoId);

            $bitacoras = BitacoraPago::with(['user.role'])
                ->where('pago_id', $pago->id)
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data'    => $bitacoras,
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error listando bitácora de pago: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'No se pudo obtener la bitácora.',
                'error'   => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Obtiene las notas de bitácora asociadas a un registro general.
     */
    public static function listarPorRegistro(int $registroId): JsonResponse
    {
        try {
            $registro = Registro::findOrFail($registroId);

            $bitacoras = BitacoraPago::with(['user.role'])
                ->where('registro_id', $registro->id)
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data'    => $bitacoras,
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error listando bitácora de registro: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'No se pudo obtener la bitácora.',
                'error'   => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Registra una nueva entrada en la bitácora para un pago.
     */
    public static function crearEntrada(Request $request, int $pagoId): JsonResponse
    {
        try {
            $request->validate([
                'descripcion' => 'required|string|min:3',
            ]);

            $pago = Pago::findOrFail($pagoId);
            $user = $request->user() ?? auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado para registrar en la bitácora.',
                ], 401);
            }

            $rolNombre = $user->role?->name;
            if (!$rolNombre) {
                $rolNombre = $user->isAdmin() ? 'Admin' : 'User';
            }

            $bitacora = BitacoraPago::create([
                'pago_id'            => $pago->id,
                'registro_id'        => $pago->registro_id,
                'user_id'            => $user->id,
                'rol_nombre'         => $rolNombre,
                'tipo_contacto'      => $request->input('tipo_contacto', 'Llamada telefónica'),
                'contacto_proveedor' => $request->input('contacto_proveedor'),
                'descripcion'        => trim($request->input('descripcion')),
                'fecha_contacto'     => $request->input('fecha_contacto', now()),
            ]);

            $bitacora->load('user.role');

            return response()->json([
                'success' => true,
                'message' => 'Nota registrada en la bitácora exitosamente.',
                'data'    => $bitacora,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos para la bitácora.',
                'errors'  => $ve->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error("Error guardando bitácora: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'No se pudo guardar la nota en la bitácora.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
