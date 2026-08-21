<?php

namespace App\Http\Services\Statistic;

use App\Http\Services\Const\ObtenerPersonasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Persona;
use App\Models\Ministerio;
use App\Models\Evento;
use App\Models\TipoCaso; // ¡Importante: se añade el modelo TipoCaso!

class EstadisticaParticipacionService
{
    /**
     * Obtiene un resumen estadístico de la participación de personas basado en estatus de caso y tipo de caso.
     *
     * @param string|null $fechaDesde Fecha de inicio para el rango de búsqueda (YYYY-MM-DD).
     * @param string|null $fechaHasta Fecha de fin para el rango de búsqueda (YYYY-MM-DD).
     * @param int $tipo_caso_id ID del tipo de caso para filtrar, o 0 para no aplicar filtro.
     * @return \Illuminate\Http\JsonResponse|array Resumen de datos estadísticos o mensaje de error.
     */

    static public function GetResumenData($fechaDesde = null, $fechaHasta = null, $tipo_caso_id = 0) {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $ministerio_id = $user->persona->ministerio_id;
        $data = [];

        if($ministerio_id == 25) $data = self::DataOAC($fechaDesde, $fechaHasta, $tipo_caso_id, $user);
        else if($ministerio_id == 28){
            $dataOAC = self::DataOAC($fechaDesde, $fechaHasta, $tipo_caso_id, $user);
            $dataAdmin = self::DataAdmin($fechaDesde, $fechaHasta, $tipo_caso_id, $user);
            $data = array_merge($dataOAC, $dataAdmin);
        }

        return response()->json($data, 200);
    }

    static public function DataAdmin($fechaDesde = null, $fechaHasta = null, $tipo_caso_id = 0, $user = null){
            return [
            'g' => ['Total de Casos Registrados', 0, '#80B0EC'], // Casos con cualquier estatus
            'h' => ['Casos En Trámite', 0, '#4B7EB6'],
            'i' => ['Casos Especiales', 0, '#609053'],
            'j' => ['Casos Normales', 0, '#FFA500'],
            'k' => ['Casos Regularizados', 0, '#8d1d1dff'],

        ];
    }
    
    static public function DataOAC($fechaDesde = null, $fechaHasta = null, $tipo_caso_id = 0, $user = null)
    {
   
        // Obtén las personas que cumplen con los criterios generales
        // Asegúrate de pasar el usuario si ObtenerPersonasService lo necesita
        $personasQuery = ObtenerPersonasService::obtenerPersonas($user); 

        // Clona la consulta base para cada filtro
        $baseQueryForCounts = clone $personasQuery;

        // Función auxiliar para aplicar los filtros de fecha y tipo de caso
        $applyFilters = function ($query, $fechaDesde, $fechaHasta, $tipo_caso_id) {
            // Aplicar filtro de fecha
            if ($fechaDesde && $fechaHasta) {
                $query->whereDate('tbl_registros.created_at', '>=', $fechaDesde)
                      ->whereDate('tbl_registros.created_at', '<=', $fechaHasta);
            } else {
                $query->whereDate('tbl_registros.created_at', '=', date('Y-m-d'));
            }

            // Aplicar filtro por tipo de caso si $tipo_caso_id no es 0
            if ($tipo_caso_id != 0) {
                $query->where('id_tipo_caso', $tipo_caso_id);
            }
        };

        // Conteo total de personas (antes de cualquier filtro de registro)
        $countTotal = $baseQueryForCounts->count();
        
        // --- Conteo de Casos Registrados (cualquier estatus) ---
        $totalCasosQuery = clone $baseQueryForCounts;
        $totalCasosQuery->whereHas('registrosEventoActivo', function ($query) use ($fechaDesde, $fechaHasta, $tipo_caso_id, $applyFilters) {
            $applyFilters($query, $fechaDesde, $fechaHasta, $tipo_caso_id);
        });

        // --- Conteo de Casos Orientados ---
        $totalOrientadosQuery = clone $baseQueryForCounts;
        $totalOrientadosQuery->whereHas('registrosEventoActivo', function ($query) use ($fechaDesde, $fechaHasta, $tipo_caso_id, $applyFilters) {
            $query->where('estatus_caso', 'Orientado');
            $applyFilters($query, $fechaDesde, $fechaHasta, $tipo_caso_id);
        });

        // --- Conteo de Casos En Trámite ---
        $totalEnTramiteQuery = clone $baseQueryForCounts;
        $totalEnTramiteQuery->whereHas('registrosEventoActivo', function ($query) use ($fechaDesde, $fechaHasta, $tipo_caso_id, $applyFilters) {
            $query->where('estatus_caso', 'En Tramite');
            $applyFilters($query, $fechaDesde, $fechaHasta, $tipo_caso_id);
        });

        // --- Conteo de Casos Resultado Directo ---
        // Renombrado de 'totalRechazadosQuery' para mayor claridad
        $totalResultadoDirectoQuery = clone $baseQueryForCounts;
        $totalResultadoDirectoQuery->whereHas('registrosEventoActivo', function ($query) use ($fechaDesde, $fechaHasta, $tipo_caso_id, $applyFilters) {
            $query->where('estatus_caso', 'Resultado Directo');
            $applyFilters($query, $fechaDesde, $fechaHasta, $tipo_caso_id);
        });

        // --- Conteo de Casos Remitidos a Otro ---
        $totalRemitidosQuery = clone $baseQueryForCounts;
        $totalRemitidosQuery->whereHas('registrosEventoActivo', function ($query) use ($fechaDesde, $fechaHasta, $tipo_caso_id, $applyFilters) {
            $query->where('estatus_caso', 'Remitido a Otro');
            $applyFilters($query, $fechaDesde, $fechaHasta, $tipo_caso_id);
        });

        // --- Conteo de Casos Cerrados ---
        $totalCerradosQuery = clone $baseQueryForCounts;
        $totalCerradosQuery->whereHas('registrosEventoActivo', function ($query) use ($fechaDesde, $fechaHasta, $tipo_caso_id, $applyFilters) {
            $query->where('estatus_caso', 'Cerrado');
            $applyFilters($query, $fechaDesde, $fechaHasta, $tipo_caso_id);
        });

        // --- Conteo de Casos Faltantes (sin registro) ---
        // Aquí no se aplica el filtro de tipo de caso, ya que "faltantes" se refiere a la ausencia de *cualquier* registro
        $totalFaltantesQuery = clone $baseQueryForCounts;
        $totalFaltantesQuery->whereDoesntHave('registrosEventoActivo', function ($query) use ($fechaDesde, $fechaHasta, $applyFilters) {
            // Solo se aplica el filtro de fechas para saber si NO tienen registros en ese rango.
            // Pasa 0 para tipo_caso_id ya que no aplica aquí.
            $applyFilters($query, $fechaDesde, $fechaHasta, 0); 
        });

        // Obtener los resultados finales
        $totalCasos = $totalCasosQuery->count();
        $totalOrientados = $totalOrientadosQuery->count();
        $totalEnTramite = $totalEnTramiteQuery->count();
        $totalResultadoDirecto = $totalResultadoDirectoQuery->count();
        $totalRemitidos = $totalRemitidosQuery->count();
        $totalCerrados = $totalCerradosQuery->count();
        $totalFaltantes = $totalFaltantesQuery->count();

        // Retornar los resultados organizados
        return [
            'a' => ['Total de Casos Registrados', $totalCasos, '#80B0EC'], // Casos con cualquier estatus
            'b' => ['Casos En Trámite', $totalEnTramite, '#4B7EB6'],
            'c' => ['Casos Orientados', $totalOrientados, '#609053'],
            'd' => ['Casos con Resultado Directo', $totalResultadoDirecto, '#c80036'],
            'e' => ['Casos Remitidos a Otro', $totalRemitidos, '#FFA500'],
            'f' => ['Casos Cerrados', $totalCerrados, '#8d1d1dff'],

        ];
    }
}