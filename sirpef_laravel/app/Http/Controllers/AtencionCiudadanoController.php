<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Services\AtencionCiudadano\CreateCasosService;
use App\Http\Services\AtencionCiudadano\IndexCasosService;
use App\Http\Services\AtencionCiudadano\ShowCasosService;
use App\Http\Services\AtencionCiudadano\GetPuntoCuentaService;
use App\Http\Services\AtencionCiudadano\GetPuntoCuentaByNumeroService;
use App\Http\Services\AtencionCiudadano\StorePuntoCuentaService;
use App\Http\Services\AtencionCiudadano\ObtenerCedulaService;
use App\Http\Services\AtencionCiudadano\AtencionCiudadanoService;
use App\Http\Services\AtencionCiudadano\UpdateCasosService;
use App\Http\Services\AtencionCiudadano\UpdatePuntoCuentaService;
use App\Http\Services\AtencionCiudadano\UpdatePuntoCuentaEstatusService;
use App\Http\Services\AtencionCiudadano\BuscarSeguimientosService;
use App\Http\Services\AtencionCiudadano\GetAllEstatusTramiteService;


use App\Http\Services\AtencionCiudadano\BuscarProveedorService;
use App\Http\Services\AtencionCiudadano\BuscarRegistroPorPuntoService;
use App\Http\Services\AtencionCiudadano\StorePagoService;
use App\Http\Services\AtencionCiudadano\GetEstatusPagoService;
use App\Http\Services\AtencionCiudadano\IndexPagoService;


class AtencionCiudadanoController extends Controller
{
    public function index(Request $request)
    {
        return IndexCasosService::index($request);
    }

    public function create(Request $request)
    {
        return CreateCasosService::create($request);
    }

    public function show($id)
    {
        return ShowCasosService::show($id);
    }

        public function updateCaso(Request $request, $registroId)
    {
        return UpdateCasosService::updateCaso($request, $registroId);
    }

    public function findByCedula(string $cedula)
    {
        return ObtenerCedulaService::obtenerPorCedula($cedula);
    }

    public function getPuntoCuenta(int $id): JsonResponse
    {
        return GetPuntoCuentaService::getPuntoCuenta($id);
    }

    public function getPuntoCuentaByNumero(string $numero): JsonResponse
    {
        return GetPuntoCuentaByNumeroService::getByNumero($numero);
    }

    public function store(Request $request, $registroId)
    {
        return StorePuntoCuentaService::crearPuntoCuenta($request, $registroId);
    }

    public function actualizarEstatus(int $registroId,int $estatusTramiteId, Request $request)
    {
        return AtencionCiudadanoService::actualizarEstatus($registroId,$estatusTramiteId,$request);
    }

     public function enviarRevision(int $registroId): JsonResponse
    {
        return AtencionCiudadanoService::enviarRevision($registroId);
    }

    public function updatePuntoCuenta(Request $request, int $puntoCuentaId): JsonResponse
    {
        return UpdatePuntoCuentaService::updatePuntoCuenta($request, $puntoCuentaId);
    }


        public function PuntoCuentaEstatus(int $puntoCuentaId): JsonResponse
    {
        return UpdatePuntoCuentaEstatusService::EstatusPuntoCuenta($puntoCuentaId);
    }

       /*public function showSeguimientos(int $registroId, BuscarSeguimientosService $buscarSeguimientosService): JsonResponse
    {
        return $buscarSeguimientosService->showSeguimientos($registroId);
    }*/

       public function showSeguimientos(int $registroId): JsonResponse
    {
        return BuscarSeguimientosService::showSeguimientos($registroId);
    }

        public function getAllEstatusTramite(GetAllEstatusTramiteService $getAllEstatusTramiteService): JsonResponse
    {
        return $getAllEstatusTramiteService->getAllEstatusTramite();
    }


    public function buscarProveedor(string $documento): JsonResponse
    {
        return BuscarProveedorService::buscarPorDocumento($documento);
    }


    public function obtenerDatosPagoPorPunto(string $numeroPunto): JsonResponse
    {
        return BuscarRegistroPorPuntoService::obtenerDataParaPago($numeroPunto);
    }


    public function storePago(Request $request, int $registroId): JsonResponse
    {
        return StorePagoService::crearPago($request, $registroId);
    }

    public function getEstatusPagos(): JsonResponse
    {
        return GetEstatusPagoService::getAllEstatusPago();
    }

    public function indexPagos(Request $request): JsonResponse
    {
        return IndexPagoService::index($request);
    }
}
