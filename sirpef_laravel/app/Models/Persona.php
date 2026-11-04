<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Persona extends Model

{
    use HasFactory, SoftDeletes;

    protected $table = 'tbl_personas';

    protected $fillable = [
        'nombre_completo',
        'cedula',
        'direccion',
        'cargo',
        'telefono',
        'sexo',
        'fecha_nacimiento',
        'correo_electronico',
        'estatus',
        'parroquia_id',
        'centro_id',
        'user_id',
        'ministerio_id',
        'entes_id',
        'uni_ads_id',
        'tipo_empleado_id',
        'causa_pension',
        'telefono2',
        'pais_id'
    ];

    public function padre()
    {
        return $this->belongsTo(Persona::class, 'autorizado_id');
    }


    public function hijos()
    {
        return $this->hasMany(Persona::class, 'autorizado_id');
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function parroquia()
    {
        return $this->belongsTo(Parroquia::class, 'parroquia_id');
    }


    public function centro()
    {
        return $this->belongsTo(Centro::class, 'centro_id');
    }


    public function ministerio()
    {
        return $this->belongsTo(Ministerio::class, 'ministerio_id');
    }


/*     public function ente()
    {
        return $this->belongsTo(Ente::class, 'entes_id');
    }


    public function unidadAdscrita()
    {
        return $this->belongsTo(UnidadAdscrita::class, 'uni_ads_id');
    } */

    public function eventos()
    {
        return $this->belongsToMany(Evento::class, 'tb_evento_persona', 'persona_id', 'evento_id');
    }

    public function auditorias()
    {
        return $this->hasMany(Auditoria::class, 'persona_id');
    }

    public function tipoEmpleado()
    {
        return $this->belongsTo(TipoEmpleado::class, 'tipo_empleado_id');
    }


    public function pais()
    {
        return $this->belongsTo(Pais::class, 'pais_id');
    }

    // Add this to your Persona model
public function registrosEventoActivo()
{
    return $this->hasManyThrough(Registro::class, EventoPersona::class, 'persona_id', 'evento_persona_id')
                ->where('evento_id', auth()->user()->configUser->evento_activo); // Asegúrate de filtrar por el evento activo.
}


}