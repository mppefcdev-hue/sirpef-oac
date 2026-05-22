<?php 

namespace App\Http\Services\AtencionCiudadano;

use Illuminate\Http\{
    Request,
    JsonResponse
};
use App\Models\Persona;
use App\Models\EventoPersona;
use App\Models\Evento;
use App\Models\Registro;
use App\Models\Recaudo;
use App\Models\TipoCaso;
use App\Models\Auditoria; // Importa el modelo Auditoria
use App\Models\UnidadAdscrita; // Importa el modelo UnidadAdscrita
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log; // Importa Log para depuración

class CreateCasosService {

    static public function create(Request $request)
    {
        return DB::transaction(function () use ($request) {
            try {
                // Verificación de usuario autenticado
                $user = auth()->user();
                if (!$user) {
                    return response()->json(['message' => 'No autorizado', 'success' => false], 401);
                }

                // Configuración del usuario
                $configUser = $user->configUser;
                if (!$configUser) {
                    return response()->json(['message' => 'Configuración de usuario no encontrada', 'success' => false], 404);
                }

                // Validar evento activo
                $eventoActivo = $configUser->evento_activo;
                $evento = Evento::find($eventoActivo);
                
                if (!$evento || $evento->estatus !== true) {
                    return response()->json(['message' => 'Evento no activo o no encontrado', 'success' => false], 400);
                }

                // Preparar reglas de validación
                $validationRules = [
                    'nombre_completo' => 'required|string|max:255',
                    'sexo' => 'required|string|max:1',
                    'fecha_nacimiento' => 'required|date',
                    'cedula' => 'required|string',
                    'telefono' => 'required|string|max:20',
                    'descripcion' => 'required|string',
                    'tipo_caso_id' => 'nullable|integer|exists:tbl_tipo_caso,id',
                ];

                // Validación condicional para tablas relacionadas
                if (Schema::hasTable('tbl_parroquias')) {
                    $validationRules['parroquia'] = 'required|integer|exists:tbl_parroquias,id';
                } else {
                    $validationRules['parroquia'] = 'required|integer';
                }

                if (Schema::hasTable('tb_ministerio')) {
                    $validationRules['unidad_adscrita'] = 'required|integer|exists:tb_ministerio,id';
                } else {
                    $validationRules['unidad_adscrita'] = 'required|integer';
                }


                // Validación de datos básicos
                $validatedData = $request->validate($validationRules);

                // Validación de recaudos (si existen)
                if ($request->has('recaudos')) {
                    $request->validate([
                        'recaudos' => 'array',
                        'recaudos.*.nombre' => 'required|string|max:255',
                        'recaudos.*.archivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:204800'
                    ]);
                }

                // Buscar o crear persona
                $persona = Persona::updateOrCreate(
                    ['cedula' => $validatedData['cedula']],
                    [
                        'nombre_completo' => $validatedData['nombre_completo'],
                        'telefono' => $validatedData['telefono'],
                        'fecha_nacimiento' => $validatedData['fecha_nacimiento'],
                        'sexo' => $validatedData['sexo'],
                        'parroquia_id' => $validatedData['parroquia'],
                        'ministerio_id' => $validatedData['unidad_adscrita'],
                        'tipo_empleado_id' => 7
                    ]
                );

                // Buscar o crear relación evento-persona
                $eventoPersona = EventoPersona::firstOrCreate(
                    ['persona_id' => $persona->id, 'evento_id' => $eventoActivo],
                    [
                        'estatus' => 'activo'
                    ]
                );


                // Crear registro principal
                $registro = Registro::create([
                    'evento_persona_id' => $eventoPersona->id,
                    'voto' => null,
                    'descripcion' => $validatedData['descripcion'],
                    'hora_voto' => now()->format('H:i:s'),
                    'observacion' => null,
                    'referencia' => null,
                    'id_tipo_caso' => $validatedData['tipo_caso_id'] ?? null,
                    'estatus_caso' => 'En Tramite'
                ]);

                // Procesar recaudos (si existen)
                $recaudosSubidos = [];
                if ($request->has('recaudos')) {
                    foreach ($request->recaudos as $recaudoData) {
                        $archivo = $recaudoData['archivo'];
                        $nombre = $recaudoData['nombre'];
                        
                        // Guardar archivo
                        $path = $archivo->store('recaudos', 'public');
                        
                        // Crear registro en base de datos con mime_type
                        $recaudo = Recaudo::create([
                            'nombre' => $nombre,
                            'path' => $path,
                            'registro_id' => $registro->id,
                            'mime_type' => $archivo->getMimeType()
                        ]);
                        
                        $recaudosSubidos[] = $recaudo;
                    }
                }

                // --- INICIO: Lógica de Auditoría ---

                $unidadActivaNombre = 'No asignada'; 
                // Obtener el nombre de la unidad adscrita si el usuario tiene una configurada
                if ($user->configUser && !empty($user->configUser->unid_activa)) {
                    $unidActivaIds = json_decode($user->configUser->unid_activa, true);
                    if (is_array($unidActivaIds) && !in_array(0, $unidActivaIds) && !empty($unidActivaIds[0])) {
                        $unidadAdscrita = UnidadAdscrita::find($unidActivaIds[0]);
                        if ($unidadAdscrita) {
                            $unidadActivaNombre = $unidadAdscrita->nombre;
                        }
                    }
                }
                
                $auditoria = new Auditoria();
                $auditoria->user_id = $user->id;
                $auditoria->persona_id = $persona->id;
                $auditoria->evento_id = $eventoActivo;
                $auditoria->descripcion = "El usuario {$user->name} creó un nuevo caso para la persona {$persona->nombre_completo} (C.I: {$persona->cedula}). Registro ID: {$registro->id}.";
                $auditoria->save();

                // --- FIN: Lógica de Auditoría ---

                return response()->json([
                    'message' => 'Caso y recaudos creados exitosamente',
                    'data' => [
                        'persona' => $persona,
                        'evento_persona' => $eventoPersona,
                        'registro' => $registro->load('tipoCaso'),
                        'recaudos' => $recaudosSubidos
                    ],
                    'success' => true
                ], 201);

            } catch (ValidationException $e) {
                DB::rollBack();
                Log::error("Error de validación en CreateCasosService: " . json_encode($e->errors()));
                return response()->json([
                    'message' => 'Error de validación',
                    'errors' => $e->errors(),
                    'success' => false
                ], 422);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Error general al crear el caso en CreateCasosService: " . $e->getMessage() . " - " . $e->getTraceAsString());
                return response()->json([
                    'message' => 'Error al crear el caso',
                    'error' => $e->getMessage(),
                    'success' => false
                ], 500);
            }
        });
    }


        static public function createProxy(Request $request)
    {
        return DB::transaction(function () use ($request) {
            try {
                // Validar evento activo
                $evento = Evento::where('estatus', true)->first();

                if (!$evento || $evento->estatus !== true) {
                    return response()->json(['message' => 'Evento no activo o no encontrado', 'success' => false], 400);
                }

                // Preparar reglas de validación
                $validationRules = [
                    'nombre_completo' => 'required|string|max:255',
                    'sexo' => 'required|string|max:1',
                    'fecha_nacimiento' => 'required|date',
                    'cedula' => 'required|string',
                    'telefono' => 'required|string|max:20',
                    'descripcion' => 'required|string',
                    'tipo_caso_id' => 'nullable|integer|exists:tbl_tipo_caso,id',
                ];

                // Validación condicional para tablas relacionadas
                if (Schema::hasTable('tbl_parroquias')) {
                    $validationRules['parroquia'] = 'required|integer|exists:tbl_parroquias,id';
                } else {
                    $validationRules['parroquia'] = 'required|integer';
                }

                if (Schema::hasTable('tb_ministerio')) {
                    $validationRules['unidad_adscrita'] = 'required|integer|exists:tb_ministerio,id';
                } else {
                    $validationRules['unidad_adscrita'] = 'required|integer';
                }


                // Validación de datos básicos
                $validatedData = $request->validate($validationRules);

                // Validación de recaudos (si existen)
                if ($request->has('recaudos')) {
                    $request->validate([
                        'recaudos' => 'array',
                        'recaudos.*.nombre' => 'required|string|max:255',
                        'recaudos.*.archivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:204800'
                    ]);
                }

                // Buscar o crear persona
                $persona = Persona::updateOrCreate(
                    ['cedula' => $validatedData['cedula']],
                    [
                        'nombre_completo' => $validatedData['nombre_completo'],
                        'telefono' => $validatedData['telefono'],
                        'fecha_nacimiento' => $validatedData['fecha_nacimiento'],
                        'sexo' => $validatedData['sexo'],
                        'parroquia_id' => $validatedData['parroquia'],
                        'ministerio_id' => $validatedData['unidad_adscrita'],
                        'tipo_empleado_id' => 7
                    ]
                );

                // Buscar o crear relación evento-persona
                $eventoPersona = EventoPersona::firstOrCreate(
                    ['persona_id' => $persona->id, 'evento_id' => $evento->id],
                    [
                        'estatus' => 'activo'
                    ]
                );


                // Crear registro principal
                $registro = Registro::create([
                    'evento_persona_id' => $eventoPersona->id,
                    'voto' => null,
                    'descripcion' => $validatedData['descripcion'],
                    'hora_voto' => now()->format('H:i:s'),
                    'observacion' => null,
                    'referencia' => null,
                    'id_tipo_caso' => $validatedData['tipo_caso_id'] ?? null,
                    'estatus_caso' => 'En Tramite'
                ]);

                // Procesar recaudos (si existen)
                $recaudosSubidos = [];
                if ($request->has('recaudos')) {
                    foreach ($request->recaudos as $recaudoData) {
                        $archivo = $recaudoData['archivo'];
                        $nombre = $recaudoData['nombre'];
                        
                        // Guardar archivo
                        $path = $archivo->store('recaudos', 'public');
                        
                        // Crear registro en base de datos con mime_type
                        $recaudo = Recaudo::create([
                            'nombre' => $nombre,
                            'path' => $path,
                            'registro_id' => $registro->id,
                            'mime_type' => $archivo->getMimeType()
                        ]);
                        
                        $recaudosSubidos[] = $recaudo;
                    }
                }

                return response()->json([
                    'message' => 'Caso y recaudos creados exitosamente',
                    'data' => [
                        'persona' => $persona,
                        'evento_persona' => $eventoPersona,
                        'registro' => $registro->load('tipoCaso'),
                        'recaudos' => $recaudosSubidos
                    ],
                    'success' => true
                ], 201);

            } catch (ValidationException $e) {
                DB::rollBack();
                Log::error("Error de validación en CreateCasosService: " . json_encode($e->errors()));
                return response()->json([
                    'message' => 'Error de validación',
                    'errors' => $e->errors(),
                    'success' => false
                ], 422);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Error general al crear el caso en CreateCasosService: " . $e->getMessage() . " - " . $e->getTraceAsString());
                return response()->json([
                    'message' => 'Error al crear el caso',
                    'error' => $e->getMessage(),
                    'success' => false
                ], 500);
            }
        });
    }
}