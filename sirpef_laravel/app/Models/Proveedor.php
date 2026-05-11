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
        'registro_id',
        'memorandum_id'
    ];

    public function memorandum()
    {
        return $this->belongsTo(Memorandum::class, 'memorandum_id');
    }
}
