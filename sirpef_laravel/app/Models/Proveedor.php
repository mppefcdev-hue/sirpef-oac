<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory;

    protected $table = 'tbl_proveedor';

    protected $fillable = [
        'cedula_rif',
        'nombre',
    ];

    public function pagosRelacionados()
    {
        return $this->hasMany(PagoProveedor::class, 'proveedor_id');
    }

    public function pagos()
    {
        return $this->belongsToMany(Pago::class, 'tbl_pago_proveedor', 'proveedor_id', 'pago_id')
            ->withPivot('monto_relacionado', 'memorandum_id')
            ->withTimestamps();
    }

    public function memorandums()
    {
        return $this->belongsToMany(Memorandum::class, 'tbl_pago_proveedor', 'proveedor_id', 'memorandum_id')
            ->withPivot('monto_relacionado', 'pago_id')
            ->withTimestamps();
    }
}
