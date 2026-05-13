<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthMenuController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\AvatarController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\MigracionController;
use App\Http\Controllers\EstadisticaController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\ConstController;
use App\Http\Controllers\NewLoginController;
use App\Http\Controllers\FeDeVidaController;
use App\Http\Controllers\AtencionCiudadanoController;
use App\Http\Controllers\TipoCasoController;
use App\Http\Controllers\MemorandumController;

use App\Http\Controllers\PersonaAutorizadaController;

use App\Http\Controllers\Configurations\{EnteController};

Route::post('/login', NewLoginController::class);
Route::post('/sanctum/token', TokenController::class);

//Route::get('/findByCedula/fedevida/search/{cedula}',[ ConstController::class, 'checkpdf']);
//Route::get('/findByCedula/fedevida/{cedula}', [ConstController::class, 'obtenerPorCedulaFeDeVida']);
//
//Route::post('/feDeVida/{id}', [FeDeVidaController::class, 'actualizarJubilado']);
//
//Route::get('/unids/ministerio', [EstadisticaController::class, 'getunidadesAdscritasGeneral']);

//Route::get('/registro/getpaises', [ConfiguracionController::class, 'getPaises']);
//Route::get('/registro/getdireccion', [ConfiguracionController::class, 'getDireccion']);
//Route::get('/registro/municipio/{estado}', [ConfiguracionController::class, 'getMunicipio']);
//Route::get('/registro/parroquia/{municipio}', [ConfiguracionController::class, 'getParroquia']);
//
//Route::get('/registro/unidadAdscritas', [ConfiguracionController::class, 'getUnidadAdscrita']);
//

//Route::get('/findByCedula/{cedula}', [ConstController::class, 'findByCedula']);

Route::middleware(['auth:sanctum'])->group(function () {
    //Route::prefix('users')->middleware(['role:admin'])->group(function () {

    Route::get('/whoami', [UserController::class, 'getMe']);



 Route::prefix('oac')->group(function () {


    //casos
    Route::get('/index', [AtencionCiudadanoController::class, 'index']);
    Route::post('/create', [AtencionCiudadanoController::class, 'create']);
    Route::get('/show/{id}', [AtencionCiudadanoController::class, 'show']);
    Route::post('/casos-estatus/{registroId}/{estatusTramiteId}', [AtencionCiudadanoController::class, 'actualizarEstatus']);
    Route::put('/casos-revision/{registroId}', [AtencionCiudadanoController::class, 'enviarRevision']);
    Route::post('/casos/{registroId}', [AtencionCiudadanoController::class, 'updateCaso']);
    Route::get('/historial/{registroId}', [AtencionCiudadanoController::class, 'showSeguimientos']);
    Route::get('/estatus-tramite', [AtencionCiudadanoController::class, 'getAllEstatusTramite']);
    Route::delete('/delete-caso/{id}', [RegistroController::class, 'DeleteVote']);


    //punto de cuenta
    Route::get('/punto-cuenta/{id}', [AtencionCiudadanoController::class, 'getPuntoCuenta']);
    Route::get('/punto-cuenta-numero/{numero}', [AtencionCiudadanoController::class, 'getPuntoCuentaByNumero'])->where('numero', '.*');
    Route::post('/crear-punto/{registroId}', [AtencionCiudadanoController::class, 'store']);
    Route::put('/puntos-cuenta/{id}', [AtencionCiudadanoController::class, 'updatePuntoCuenta']);
    Route::put('estatus-puntos-cuenta/{puntoCuentaId}', [AtencionCiudadanoController::class, 'PuntoCuentaEstatus']);




    Route::get('/findByCedula/{cedula}', [AtencionCiudadanoController::class, 'findByCedula']);

    // Memorandum
    Route::get('/memorandum', [MemorandumController::class, 'index']);
    Route::get('/memorandum/buscar-punto-cuenta/{numero}', [MemorandumController::class, 'buscarPuntoCuenta'])->where('numero', '.*');
    Route::post('/memorandum', [MemorandumController::class, 'store']);
    Route::put('/memorandum/{id}', [MemorandumController::class, 'update']);
    Route::delete('/memorandum/{id}', [MemorandumController::class, 'destroy']);

    //crud tipo de casos
    Route::get('/tipoCaso', [TipoCasoController::class, 'index']);
    Route::post('/tipoCaso', [TipoCasoController::class, 'store']);
    Route::get('/tipoCaso/{tipoCaso}', [TipoCasoController::class, 'show']);
    Route::put('/tipoCaso/{tipoCaso}', [TipoCasoController::class, 'update']);
    Route::delete('/tipoCaso/{tipoCaso}', [TipoCasoController::class, 'destroy']);


        // registro de pagos
    Route::get('/buscar-proveedor/{documento}', [AtencionCiudadanoController::class, 'buscarProveedor']);
    Route::get('/preparar-pago/{numeroPunto}', [AtencionCiudadanoController::class, 'obtenerDatosPagoPorPunto'])
     ->where('numeroPunto', '.*');
    Route::post('/registrar-pago/{registroId}', [AtencionCiudadanoController::class, 'storePago']);
    Route::get('/estatus-pagos', [AtencionCiudadanoController::class, 'getEstatusPagos']);
    // Ruta rápida sin pasar por el controlador
    Route::get('/tipos-pagos', function () {return response()->json(['success' => true,'data' => \App\Models\TipoPago::all(['id', 'nombre'])]);});
    Route::get('/pagos', [AtencionCiudadanoController::class, 'indexPagos']);
    Route::delete('/pago-proveedor/{id}', [\App\Http\Controllers\PagoProveedorController::class, 'destroy']);

 });










    Route::prefix('registro')->group(function () {


        Route::get('/getpaises', [ConfiguracionController::class, 'getPaises']);
        Route::get('/getdireccion', [ConfiguracionController::class, 'getDireccion']);
        Route::get('/municipio/{estado}', [ConfiguracionController::class, 'getMunicipio']);
        Route::get('/parroquia/{municipio}', [ConfiguracionController::class, 'getParroquia']);


        //RegistroController

        Route::get('/fedevida/search/{cedula}',[ RegistroController::class, 'ObtenerCedulaFeDeVida']);

        Route::get('/search/{cedula}',[ RegistroController::class, 'obtenerPorCedula']);


        Route::post('/vote', [RegistroController::class, 'registervote']);

        Route::post('/solicitud-estatus', [RegistroController::class, 'registervoteFedeVida']);

        Route::get('/grupal/search/{cedula}', [RegistroController::class, 'obtenerPorCedulaGrupal']);

        Route::get('/autorizado/search/{cedula}', [RegistroController::class, 'obtenerCedulaAutorizado']);



        Route::post('/multiple-votes', [RegistroController::class, 'registerMultipleVotes']);

        //Route::delete('/delete-vote/{id}', [RegistroController::class, 'DeleteVote']);

        Route::get('/send-sms', [RegistroController::class, 'sendSms']);

        Route::get('/send-message/{data}', [RegistroController::class, 'sendMessage']);

        Route::put('/{personaId}', [RegistroController::class, 'editRegistro']);

        Route::post('/register-multiple-votes-autorizado', [RegistroController::class, 'registerMultipleVotesAutorizado']);



        //PersonaAutorizadaController

        Route::get('/persona-autorizada/{cedula}', [PersonaAutorizadaController::class, 'verificarCedula']);

        //Route::post('/evento-persona/asignar/{id}/{cedula}', [EventoPersonaController::class, 'asignarPersonaAutorizada']);

        Route::post('/evento-persona/asignar', [PersonaAutorizadaController::class, 'asignarPersonaAutorizada']);

        Route::get('/solicitudes/{tipo}', [PersonaAutorizadaController::class, 'obtenerPersonasAutorizadas']);

        Route::put('/evento-persona-solicitud/estatus/{id}', [PersonaAutorizadaController::class, 'actualizarEstatus']);

        Route::post('/cortesia-entregada', [PersonaAutorizadaController::class, 'actualizarCortesiaEntregada']);

        Route::get('/obtener-cortesias', [PersonaAutorizadaController::class, 'obtenerCortesias']);


        //EstadisticaController

        Route::get('/', [EstadisticaController::class, 'index']);
        Route::get('/countgenero', [EstadisticaController::class, 'contarPersonasPorGenero']);
        Route::get('/count/{fechaDesde?}/{fechaHasta?}/{tipo_caso_id}', [EstadisticaController::class, 'GetResumenData']);
        Route::get('/fe-vida-count/{fechaDesde?}/{fechaHasta?}', [EstadisticaController::class, 'GetResumenDataFeDeVida']);


        Route::get('/registro-total', [EstadisticaController::class, 'getRegistrosPorTipoCaso']);

        Route::get('/countSex/{fechaDesde?}/{fechaHasta?}', [EstadisticaController::class, 'GetResumenDataBySex']);

        Route::get('/countSexAge/{fechaDesde?}/{fechaHasta?}', [EstadisticaController::class, 'GetResumenDataBySexAge']);


        Route::post('/obtener-unidad-adscrita', [EstadisticaController::class, 'unidadAdscritaUsuario']);

       Route::get('/get-hourly-vote-data/{fechaDesde?}/{fechaHasta?}',[EstadisticaController::class, 'GetResumenDatahora']);

        Route::get('/conteoRegistrosPorEstado/{fechaDesde?}/{fechaHasta?}',[EstadisticaController::class, 'conteoRegistrosPorEstado']);

        Route::get('/porcentaje-participacion/{fechaDesde?}/{fechaHasta?}', [EstadisticaController::class, 'calcularPorcentajeParticipacion']);

        Route::get('/porcentaje-participacion-estado/{fechaDesde?}/{fechaHasta?}', [EstadisticaController::class, 'calcularPorcentajeParticipacionPorEstado']);

        Route::get('/get_per_x_unid/{unid}/{voto_status}/{tipoCasoId?}/{fechaDesde?}/{fechaHasta?}', [EstadisticaController::class, 'filterPersonasXUnid']);
            

        Route::get('/get_per_x_estado/{unid}/{voto_status}/{fechaDesde?}/{fechaHasta?}', [EstadisticaController::class, 'filterPersonasXEstado']);

        Route::get('/treeview-stats', [EstadisticaController::class, 'getTreeviewStats']);

        //MigracionController

        Route::post('/load-excel', [MigracionController::class, 'import']);

        Route::post('/load-excel-final', [MigracionController::class, 'importFinal']);

        Route::post('/import-cedulas', [MigracionController::class, 'importCedulas']);



        //ConfiguracionController


        Route::get('/centro', [ConfiguracionController::class, 'getCentro']);

        Route::post('/create', [ConfiguracionController::class, 'create']);

        //autorizadocontroller





        //EventoController

        Route::get('/evento', [EventoController::class, 'getAllEventos']);

        Route::put('/activarEvento/{eventoId}', [EventoController::class, 'setEventoActivo']);

        Route::get('/get-ministerios', [EventoController::class, 'getMinisterios']);

        Route::post('/evento/store', [EventoController::class, 'storeEvento']);

        Route::get('/evento/edit/{id}', [EventoController::class, 'editEvento']);

        Route::put('/evento/update/{id}', [EventoController::class, 'updateEvento']);
        Route::put('/evento/reset/{id}', [EventoController::class, 'resetEvento']);

        Route::put('/evento/{id}/status', [EventoController::class, 'changeEventoStatus']);

        Route::post('/evento/carga-masiva/{evento_id}', [EventoController::class, 'cargaMasivaEvento']);



        Route::get('/eventos/asignados', [EventoController::class, 'getEventosActivosYUltimosAsignados']);


        // routes/api.php

        Route::get('/user/info', [ConstController::class, 'getUserInfo']);


        Route::get('/unidades-adscritas', [EstadisticaController::class, 'unidadesAdscritasDelUsuario']);

    });


        Route::get('/entes', [EnteController::class, 'index']);

        Route::get('/entes/create', [EnteController::class, 'create']);

        Route::post('/entes', [EnteController::class, 'store']);

        Route::get('/entes/{ente}', [EnteController::class, 'show']);

        Route::get('/entes/{ente}/edit', [EnteController::class, 'edit']);

        Route::put('/entes/{ente}', [EnteController::class, 'update']);

        Route::delete('/entes/{ente}', [EnteController::class, 'destroy']);


        Route::get('/ministerios', [ConfiguracionController::class, 'indexMinisterios']);

        Route::get('/ministerios/create', [ConfiguracionController::class, 'createMinisterio']);

        Route::post('/ministerios', [ConfiguracionController::class, 'storeMinisterio']);

        Route::get('/ministerios/{ministerio}', [ConfiguracionController::class, 'showMinisterio']);

        Route::get('/ministerios/{ministerio}/edit', [ConfiguracionController::class, 'editMinisterio']);

        Route::put('/ministerios/{ministerio}', [ConfiguracionController::class, 'updateMinisterio']);

        Route::delete('/ministerios/{ministerio}', [ConfiguracionController::class, 'destroyMinisterio']);



        Route::get('/tipos', [ConfiguracionController::class, 'indexTipos']);

        Route::get('/tipos/create', [ConfiguracionController::class, 'createTipo']);

        Route::post('/tipos', [ConfiguracionController::class, 'storeTipo']);

        Route::get('/tipos/{tipo}', [ConfiguracionController::class, 'showTipo']);

        Route::get('/tipos/{tipo}/edit', [ConfiguracionController::class, 'editTipo']);

        Route::put('/tipos/{tipo}', [ConfiguracionController::class, 'updateTipo']);

        Route::delete('/tipos/{tipo}', [ConfiguracionController::class, 'destroyTipo']);


        Route::get('/auditorias', [ConfiguracionController::class, 'indexAuditorias']);

        Route::get('/auditorias/create', [ConfiguracionController::class, 'createAuditoria']);

        Route::post('/auditorias', [ConfiguracionController::class, 'storeAuditoria']);

        Route::get('/auditorias/{auditoria}', [ConfiguracionController::class, 'showAuditoria']);

        Route::get('/auditorias/{auditoria}/edit', [ConfiguracionController::class, 'editAuditoria']);

        //Route::put('/auditorias/{auditoria}', [ConfiguracionController::class, 'updateAuditoria']);

        Route::delete('/auditorias/{auditoria}', [ConfiguracionController::class, 'destroyAuditoria']);

    });

    Route::prefix('users')->group(function () {
        Route::get('/auth', AuthController::class);
        Route::get('/auth-menu', AuthMenuController::class);
        Route::get('/{user}', [UserController::class, 'show']);
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::post('/{user}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class,'destroy']);
        Route::post('/auth/avatar', [AvatarController::class, 'store']);
    });

    Route::prefix('menus')->group(function () {
        Route::get('/', [MenuController::class, 'index']);
        Route::get('/children/{menuId}', [MenuController::class, 'children']);
        Route::post('/', [MenuController::class, 'store']);
        Route::put('/{menu}', [MenuController::class, 'update']);
        Route::delete('/{id}', [MenuController::class,'destroy']);
    });

    Route::prefix('roles')->group(function () {
        Route::get('/helperTables', fn() => response()->json([
            "roles" => \App\Models\Role::get()
        ], 200));// routes/api.php

        Route::get('/unidadAdscritaGet', fn() => response()->json([
            "unidadAdscrita" => \App\Models\UnidadAdscrita::get(['id','nombre'])
        ], 200));// routes/api.php

        Route::get('/{role}', [RoleController::class, 'show']);
        Route::get('/', [RoleController::class, 'index']);
        Route::post('/', [RoleController::class, 'store']);
        Route::put('/{role}', [RoleController::class, 'update']);
        Route::delete('/{id}', [RoleController::class,'destroy']);
    });


Route::prefix('error')->group(function () {
    Route::get('/not-auth', function(){
        abort(403, 'This action is not authorized.');
    });

    Route::get('/not-found', function(){
        abort(404, 'Page not found.');
    });

    Route::get('/', function(){
        abort(500, 'Something went wrong');
    });
    Route::get('/custom', function(){
        throw new \App\Exceptions\CustomException('Error: Levi Strauss & CO.', 501);
    });
});



//----------------------------------------------------------------------------//

