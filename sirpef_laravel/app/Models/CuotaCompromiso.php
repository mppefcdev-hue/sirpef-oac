<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CuotaCompromiso extends Model
{
    use HasFactory;

    protected $table = 'tbl_cuotas_compromiso';

    protected $fillable = [
        'year',
        'mes',
        'monto',
    ];
}
