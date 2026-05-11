<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recaudo extends Model
{
    use HasFactory;

    protected $table = 'tbl_recaudos';

    protected $fillable = [
        'nombre',
        'path',
        'registro_id',
        'pago_id',
        'mime_type' 
    ];

    public function registro()
    {
        return $this->belongsTo(Registro::class);
    }
}
