<?php

namespace App\Http\Services\Statistic;

use App\Http\Services\Const\ObtenerPersonasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Persona;
use App\Models\Ministerio;
use App\Models\Evento;
use App\Models\TipoCaso; // ¡Importante: se añade el modelo TipoCaso!
use App\Models\Pago;
use App\Models\Recaudo;

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

        $ministerio_id = $user->persona->ministerio_id ?? null;
        $data = [];

        if ($ministerio_id == 25) {
            $data = self::DataOAC($fechaDesde, $fechaHasta, $tipo_caso_id, $user);
        } else if ($ministerio_id == 19) {
            $data = self::DataOGA($fechaDesde, $fechaHasta, $tipo_caso_id, $user, true);
        } else if ($ministerio_id == 28) {
            $dataOAC = self::DataOAC($fechaDesde, $fechaHasta, $tipo_caso_id, $user);
            $dataOGA = self::DataOGA($fechaDesde, $fechaHasta, $tipo_caso_id, $user, false);
            $data = array_merge($dataOAC, $dataOGA);
        } else {
            // Por defecto si es otro ministerio, intentar con OAC
            $data = self::DataOAC($fechaDesde, $fechaHasta, $tipo_caso_id, $user);
        }

        return response()->json($data, 200);
    }

    static public function DataOGA($fechaDesde = null, $fechaHasta = null, $tipo_caso_id = 0, $user = null, $standalone = true)
    {
        $baseQuery = Pago::query();

        // Filtro de fechas si se proporcionan
        if ($fechaDesde && $fechaHasta && $fechaDesde !== 'null' && $fechaHasta !== 'null') {
            $baseQuery->where(function ($q) use ($fechaDesde, $fechaHasta) {
                $q->whereDate('created_at', '>=', $fechaDesde)
                  ->whereDate('created_at', '<=', $fechaHasta)
                  ->orWhere(function ($q2) use ($fechaDesde, $fechaHasta) {
                      $q2->whereNotNull('fecha_orden_pago')
                         ->whereDate('fecha_orden_pago', '>=', $fechaDesde)
                         ->whereDate('fecha_orden_pago', '<=', $fechaHasta);
                  });
            });
        }

        // Filtro por tipo de caso si no es 0
        if ($tipo_caso_id && $tipo_caso_id != 0 && $tipo_caso_id !== 'null') {
            $baseQuery->whereHas('registro', function ($q) use ($tipo_caso_id) {
                $q->where('id_tipo_caso', $tipo_caso_id);
            });
        }

        // 1. Total general de casos OGA
        $totalCasos = (clone $baseQuery)->count();

        // 2. Casos Normales: tipo de pago seleccionado como 'normal'
        $totalNormales = (clone $baseQuery)->where(function ($q) {
            $q->where('tipo_pago_id', 2)
              ->orWhereHas('tipoPago', function ($tp) {
                  $tp->whereRaw('LOWER(nombre) LIKE ?', ['%normal%']);
              });
        })->count();

        // 3. Casos Financieros: tipo de pago seleccionado como 'financiero'
        $totalFinancieros = (clone $baseQuery)->where(function ($q) {
            $q->where('tipo_pago_id', 1)
              ->orWhereHas('tipoPago', function ($tp) {
                  $tp->whereRaw('LOWER(nombre) LIKE ?', ['%financier%']);
              });
        })->count();

        // 4. Casos Regularizados: estatus de procesados por el SIGECOF con estatus 'procesado'
        $totalRegularizados = (clone $baseQuery)->where(function ($q) {
            $q->where('estatus_pago_id', 1)
              ->orWhereHas('estatus', function ($ep) {
                  $ep->whereRaw('LOWER(nombre) LIKE ?', ['%procesado%']);
              });
        })->count();

        // 5. Casos Facturas: procesos que tienen facturas agregadas (recaudos o en descripción)
        $totalFacturas = (clone $baseQuery)->where(function ($q) {
            $q->whereHas('recaudos')
              ->orWhere('descripcion', 'LIKE', '%[Factura:%')
              ->orWhere('descripcion', 'LIKE', '%factura%');
        })->count();

        // 6. Casos sin Factura: procesos abiertos que no tienen la factura agregada
        $totalSinFactura = (clone $baseQuery)->whereDoesntHave('registro', function ($rq) {
            $rq->where('estatus_caso', 'Cerrado');
        })->where(function ($q) {
            $q->whereDoesntHave('recaudos')
              ->where(function ($dq) {
                  $dq->whereNull('descripcion')
                     ->orWhere(function ($dq2) {
                         $dq2->where('descripcion', 'NOT LIKE', '%[Factura:%')
                             ->where('descripcion', 'NOT LIKE', '%factura%');
                     });
              });
        })->count();

        // 7. Casos con reintegros: saldo deudor mayor a cero
        $totalConReintegros = (clone $baseQuery)->where('saldo_deudor', '>', 0)->count();

        // 8. Casos Cierre Administrativos: completados/cerrados en su totalidad con facturas agregadas
        $totalCierreAdmin = (clone $baseQuery)->whereHas('registro', function ($rq) {
            $rq->where('estatus_caso', 'Cerrado');
        })->where(function ($q) {
            $q->whereHas('recaudos')
              ->orWhere('descripcion', 'LIKE', '%[Factura:%')
              ->orWhere('descripcion', 'LIKE', '%factura%');
        })->count();

        if ($standalone) {
            return [
                'a' => ['Total de Casos Registrados', $totalCasos, '#80B0EC'],
                'b' => ['Casos Normales', $totalNormales, '#FFA500'],
                'c' => ['Casos Financieros', $totalFinancieros, '#4B7EB6'],
                'd' => ['Casos Regularizados', $totalRegularizados, '#609053'],
                'e' => ['Casos Facturas', $totalFacturas, '#2052C7'],
                'f' => ['Casos sin Factura', $totalSinFactura, '#E05D5D'],
                'g' => ['Casos con Reintegros', $totalConReintegros, '#D97706'],
                'h' => ['Casos Cierre Administrativos', $totalCierreAdmin, '#8d1d1dff'],
            ];
        }

        return [
            'g' => ['Total de Casos OGA', $totalCasos, '#80B0EC'],
            'h' => ['Casos Normales', $totalNormales, '#FFA500'],
            'i' => ['Casos Financieros', $totalFinancieros, '#4B7EB6'],
            'j' => ['Casos Regularizados', $totalRegularizados, '#609053'],
            'k' => ['Casos Facturas', $totalFacturas, '#2052C7'],
            'l' => ['Casos sin Factura', $totalSinFactura, '#E05D5D'],
            'm' => ['Casos con Reintegros', $totalConReintegros, '#D97706'],
            'n' => ['Casos Cierre Administrativos', $totalCierreAdmin, '#8d1d1dff'],
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