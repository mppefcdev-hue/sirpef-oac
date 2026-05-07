<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Memorandum extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tbl_memorandums';

    protected $fillable = [
        'punto_cuenta_id',
        'codigo',
        'de',
        'para',
        'asunto',
        'fecha',
        'cuerpo',
        'monto',
        'proveedor',
        'header_img',
        'footer_img',
        'firma_img',
    ];

    protected $casts = [
        'fecha' => 'date',
        'header_img' => 'string',
        'footer_img' => 'string',
        'firma_img' => 'string',
    ];

    public function puntoCuenta()
    {
        return $this->belongsTo(PuntoCuenta::class, 'punto_cuenta_id');
    }

    public function proveedores()
    {
        return $this->hasMany(Proveedor::class, 'memorandum_id');
    }
}
