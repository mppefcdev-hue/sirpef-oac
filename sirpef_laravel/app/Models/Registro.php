<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes; // ¡Importa este trait!

class Registro extends Model
{
    use SoftDeletes; // ¡Usa este trait para habilitar la eliminación suave!

    protected $table = 'tbl_registros';

    protected $fillable = [
        'user_id',
        'voto',
        'descripcion',
        'hora_voto',
        'evento_persona_id',
        'observacion',
        'referencia',
        'id_tipo_caso',
        'estatus_caso'
    ];

    public function eventoPersona()
    {
        return $this->belongsTo(EventoPersona::class, 'evento_persona_id');
    }

    public function tipoCaso()
    {
        return $this->belongsTo(TipoCaso::class, 'id_tipo_caso');
    }

     public function puntoCuenta()
    {
        return $this->belongsTo(PuntoCuenta::class, 'punto_cuenta_id');
    }

    public function proveedores()
    {
        return $this->hasMany(Proveedor::class, 'registro_id');
    }

    public function recaudos()
    {
        return $this->hasMany(Recaudo::class, 'registro_id');
    }

    public function seguimientos(): HasMany
    {
        return $this->hasMany(Seguimiento::class, 'registro_id');
    }
}
