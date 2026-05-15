<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstatusPago extends Model
{
    use HasFactory;

    protected $table = 'tbl_estatus_pagos';

    protected $fillable = [
        'nombre',
        'color',
        'descripcion'
    ];

    /**
     * Un estatus puede estar presente en muchos pagos.
     */
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'estatus_pago_id');
    }
}