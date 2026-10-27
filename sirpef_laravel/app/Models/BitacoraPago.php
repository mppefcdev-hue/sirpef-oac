<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BitacoraPago extends Model
{
    use HasFactory;

    protected $table = 'tbl_bitacora_pagos';

    protected $fillable = [
        'registro_id',
        'pago_id',
        'user_id',
        'rol_nombre',
        'tipo_contacto',
        'contacto_proveedor',
        'descripcion',
        'fecha_contacto',
    ];

    protected $casts = [
        'fecha_contacto' => 'datetime',
    ];

    /**
     * Usuario que registró el contacto.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Pago asociado.
     */
    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class, 'pago_id');
    }

    /**
     * Registro/caso asociado.
     */
    public function registro(): BelongsTo
    {
        return $this->belongsTo(Registro::class, 'registro_id');
    }
}
