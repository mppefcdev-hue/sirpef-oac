<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PuntoCuenta extends Model
{
    use HasFactory;

    protected $table = 'tbl_punto_cuenta';

    protected $fillable = [
        'presentado_a',
        'presentado_por',
        'fecha',
        'numero_punto',
        'asunto',
        'exposicion_motivos',
        'propuesta',
        'decision',
        'otras_instrucciones',
        'anexos',
        'cargo_a',
        'cargo_por',
        'resolucion_1',
        'resolucion_2',
        'estatus_pc',
        'cantidad_editada'
    ];

    protected $casts = [
        'fecha' => 'date',
        'anexos' => 'boolean'
    ];



    public function registros()
    {
        return $this->hasMany(Registro::class, 'punto_cuenta_id');
    }

    public function memorandum()
    {
        return $this->hasOne(Memorandum::class, 'punto_cuenta_id');
    }
}
