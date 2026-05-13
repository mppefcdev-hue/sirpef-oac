<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany; // <--- PASO 1: Importar esto

class Pago extends Model
{
    use HasFactory;

    protected $table = 'tbl_pagos';

    protected $fillable = [
        'orden_pago',
        'fecha_orden_pago',
        'monto',
        'descripcion',
        'fecha_pago_financiero',
        'saldo_deudor',
        'saldo_acreedor',
        'cuota_compromiso_disponible',
        'estatus_pago_id',
        'tipo_pago_id',
        'registro_id',
    ];

    /**
     * PASO 2: Definir la relación de Recaudos
     * Un Pago tiene muchos Recaudos asociados.
     */
    public function recaudos(): HasMany
    {
        return $this->hasMany(Recaudo::class, 'pago_id');
    }

    // ... tus otras relaciones (estatus, tipoPago, registro, proveedores) ...
    
    public function estatus(): BelongsTo
    {
        return $this->belongsTo(EstatusPago::class, 'estatus_pago_id');
    }

    public function tipoPago(): BelongsTo
    {
        return $this->belongsTo(TipoPago::class, 'tipo_pago_id');
    }

    public function proveedores(): BelongsToMany
    {
        return $this->belongsToMany(Proveedor::class, 'tbl_pago_proveedor', 'memorandum_id', 'proveedor_id')
                    ->withPivot('monto_relacionado')
                    ->withTimestamps();
    }
}
