<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany; // <--- PASO 1: Importar esto

use Illuminate\Database\Eloquent\SoftDeletes;

class Pago extends Model
{
    use HasFactory, SoftDeletes;

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

    protected $appends = [
        'beneficiario',
        'diagnostico',
        'nro_factura',
    ];

    public function getBeneficiarioAttribute(): ?string
    {
        return $this->registro?->eventoPersona?->persona?->nombre_completo ?? null;
    }

    public function getDiagnosticoAttribute(): ?string
    {
        return $this->registro?->descripcion 
            ?? $this->registro?->puntoCuenta?->memorandum?->cuerpo 
            ?? null;
    }

    public function getNroFacturaAttribute(): ?string
    {
        // Si hay recaudos asociados al pago o registro
        if ($this->relationLoaded('recaudos') && $this->recaudos->isNotEmpty()) {
            $recaudo = $this->recaudos->first(function ($r) {
                return stripos($r->nombre, 'factura') !== false || !empty($r->path);
            });
            return $recaudo ? $recaudo->nombre : $this->recaudos->first()->nombre;
        }

        // Si la descripción contiene el tag [Factura: ...]
        if (preg_match('/\[Factura:\s*([^\]]+)\]/i', $this->descripcion ?? '', $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * Relación de Recaudos
     */
    public function recaudos(): HasMany
    {
        return $this->hasMany(Recaudo::class, 'pago_id');
    }

    public function estatus(): BelongsTo
    {
        return $this->belongsTo(EstatusPago::class, 'estatus_pago_id');
    }

    public function tipoPago(): BelongsTo
    {
        return $this->belongsTo(TipoPago::class, 'tipo_pago_id');
    }

    public function registro(): BelongsTo
    {
        return $this->belongsTo(Registro::class, 'registro_id');
    }

    public function proveedores(): BelongsToMany
    {
        return $this->belongsToMany(Proveedor::class, 'tbl_pago_proveedor', 'pago_id', 'proveedor_id')
                    ->withPivot('monto_relacionado')
                    ->withTimestamps();
    }
}
