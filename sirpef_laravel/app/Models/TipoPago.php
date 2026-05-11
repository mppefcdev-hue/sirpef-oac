<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoPago extends Model
{
    use HasFactory;

    protected $table = 'tbl_tipo_pagos';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /**
     * Un tipo de pago puede tener muchos pagos asociados.
     */
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'tipo_pago_id');
    }
}