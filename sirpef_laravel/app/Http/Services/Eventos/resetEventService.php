<?php
namespace App\Http\Services\Eventos;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Evento;
use App\Models\EventoPersona;
use App\Models\User;

class resetEventService
{
    static public function index(Request $request, $id)
    {
        $evento = Evento::find($id);

        if (!$evento || !$evento->estatus) {
            return response()->json(['error' => 'Evento no encontrado o no esta desactivado'], 404);
        }

            try {

                #$EventoPersona

                return response()->json([
                    'mensaje' => 'Evento reestablecido con éxito',
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Error al reestablecido con evento',
                    'detalle' => $e->getMessage()
                ], 500);
            }

        return response()->json(['mensaje' => 'El evento ha sido reestablecido con éxito']);
    }
}