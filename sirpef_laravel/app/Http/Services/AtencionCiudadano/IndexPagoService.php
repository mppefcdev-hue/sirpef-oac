<?php

namespace App\Http\Services\AtencionCiudadano;

use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class IndexPagoService
{
    /**
     * Obtiene la lista de pagos con paginación, filtros y relaciones.
     */
    static public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);

            // 1. Construir la consulta con Eager Loading para evitar el problema N+1
            $query = Pago::with([
                'proveedores',
                'estatus',
                'tipoPago',
                'recaudos',
                'registro.puntoCuenta.memorandum',
                'registro.eventoPersona.persona',
            ])->orderBy('created_at', 'desc');

            // 2. Filtro por Mes (número 1-12 o formato YYYY-MM)
            if ($request->filled('mes')) {
                $mes = trim($request->mes);
                if (str_contains($mes, '-')) {
                    [$anio, $mesNum] = explode('-', $mes);
                    $query->where(function ($q) use ($anio, $mesNum) {
                        $q->where(function ($q1) use ($anio, $mesNum) {
                            $q1->whereYear('fecha_orden_pago', $anio)
                               ->whereMonth('fecha_orden_pago', $mesNum);
                        })->orWhere(function ($q2) use ($anio, $mesNum) {
                            $q2->whereNull('fecha_orden_pago')
                               ->whereYear('created_at', $anio)
                               ->whereMonth('created_at', $mesNum);
                        });
                    });
                } else {
                    $mesNum = intval($mes);
                    if ($mesNum >= 1 && $mesNum <= 12) {
                        $query->where(function ($q) use ($mesNum) {
                            $q->whereMonth('fecha_orden_pago', $mesNum)
                              ->orWhere(function ($q2) use ($mesNum) {
                                  $q2->whereNull('fecha_orden_pago')
                                     ->whereMonth('created_at', $mesNum);
                              });
                        });
                    }
                }
            }

            // 3. Filtro por Factura (número de factura o tenencia: con_factura / sin_factura)
            if ($request->filled('factura')) {
                $factura = trim($request->factura);
                if (strtolower($factura) === 'con_factura' || strtolower($factura) === 'si' || strtolower($factura) === 'true') {
                    $query->where(function ($q) {
                        $q->whereHas('recaudos')
                          ->orWhere('descripcion', 'LIKE', '%[Factura:%')
                          ->orWhere('descripcion', 'LIKE', '%factura%');
                    });
                } elseif (strtolower($factura) === 'sin_factura' || strtolower($factura) === 'no' || strtolower($factura) === 'false') {
                    $query->whereDoesntHave('recaudos')
                          ->where(function ($q) {
                              $q->whereNull('descripcion')
                                ->orWhere(function ($q2) {
                                    $q2->where('descripcion', 'NOT LIKE', '%[Factura:%')
                                       ->where('descripcion', 'NOT LIKE', '%factura%');
                                });
                          });
                } else {
                    $query->where(function ($q) use ($factura) {
                        $q->where('descripcion', 'LIKE', "%{$factura}%")
                          ->orWhereHas('recaudos', function ($rq) use ($factura) {
                              $rq->whereRaw('LOWER(nombre) LIKE ?', ['%' . strtolower($factura) . '%']);
                          });
                    });
                }
            }

            // 4. Filtro por Proveedor (Nombre o Cédula/RIF)
            if ($request->filled('proveedor')) {
                $proveedor = trim($request->proveedor);
                $query->whereHas('proveedores', function ($q) use ($proveedor) {
                    $q->whereRaw('LOWER(nombre) LIKE ?', ['%' . strtolower($proveedor) . '%'])
                      ->orWhereRaw('LOWER(cedula_rif) LIKE ?', ['%' . strtolower($proveedor) . '%']);
                });
            }

            // 5. Filtro por Paciente / Beneficiario (Nombre o Cédula)
            if ($request->filled('paciente')) {
                $paciente = trim($request->paciente);
                $query->whereHas('registro.eventoPersona.persona', function ($pq) use ($paciente) {
                    $pq->whereRaw('LOWER(nombre_completo) LIKE ?', ['%' . strtolower($paciente) . '%'])
                       ->orWhere('cedula', 'LIKE', "%{$paciente}%");
                });
            }

            // 6. Filtro por Punto de Cuenta (Número de punto)
            if ($request->filled('punto_cuenta')) {
                $puntoCuenta = trim($request->punto_cuenta);
                $query->whereHas('registro.puntoCuenta', function ($q) use ($puntoCuenta) {
                    $q->where('numero_punto', 'LIKE', "%{$puntoCuenta}%");
                });
            }

            // 7. Filtro por Orden de Pago
            if ($request->filled('orden_pago')) {
                $ordenPago = trim($request->orden_pago);
                $query->where('orden_pago', 'LIKE', "%{$ordenPago}%");
            }

            // 8. Búsqueda rápida general (search)
            if ($request->filled('search')) {
                $search = trim($request->search);
                $query->where(function ($q) use ($search) {
                    $q->where('orden_pago', 'LIKE', "%{$search}%")
                      ->orWhere('descripcion', 'LIKE', "%{$search}%")
                      ->orWhereHas('proveedores', function ($pq) use ($search) {
                          $pq->whereRaw('LOWER(nombre) LIKE ?', ['%' . strtolower($search) . '%'])
                             ->orWhereRaw('LOWER(cedula_rif) LIKE ?', ['%' . strtolower($search) . '%']);
                      })
                      ->orWhereHas('registro.eventoPersona.persona', function ($peq) use ($search) {
                          $peq->whereRaw('LOWER(nombre_completo) LIKE ?', ['%' . strtolower($search) . '%'])
                             ->orWhere('cedula', 'LIKE', "%{$search}%");
                      })
                      ->orWhereHas('registro.puntoCuenta', function ($pcq) use ($search) {
                          $pcq->where('numero_punto', 'LIKE', "%{$search}%");
                      })
                      ->orWhereHas('recaudos', function ($rq) use ($search) {
                          $rq->whereRaw('LOWER(nombre) LIKE ?', ['%' . strtolower($search) . '%']);
                      });
                });
            }

            /** @var \Illuminate\Pagination\LengthAwarePaginator $pagos */
            $pagos = $query->paginate($perPage);

            $pagosArray = $pagos->toArray();
            $links = $pagosArray['links'] ?? [];

            return response()->json([
                'success' => true,
                'data'    => $pagos->items(),
                'rows'    => $pagos->items(),
                'links'   => $links,
                'meta'    => [
                    'current_page' => $pagos->currentPage(),
                    'last_page'    => $pagos->lastPage(),
                    'per_page'     => $pagos->perPage(),
                    'total'        => $pagos->total(),
                    'links'        => $links,
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error en IndexPagoService: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'No se pudo obtener la lista de pagos.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}