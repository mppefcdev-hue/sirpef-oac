<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoProveedor extends Model
{
    use HasFactory;

    protected $table = 'tbl_pago_proveedor';

    protected $fillable = [
        'pago_id',
        'memorandum_id',
        'proveedor_id',
        'monto_relacionado',
    ];

    public function pago()
    {
        return $this->belongsTo(Pago::class, 'pago_id');
    }

    public function memorandum()
    {
        return $this->belongsTo(Memorandum::class, 'memorandum_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }
}
