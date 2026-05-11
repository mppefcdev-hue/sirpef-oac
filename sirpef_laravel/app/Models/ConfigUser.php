<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'finger_id',
        'evento_asignado',
        'oficina_asignada',
        'evento_activo',
        'unid_activa',
        'menu_ids',
        'nuevo_campo',
    ];

   

    // Relación con User
    public function users()
    {
        return $this->hasMany(User::class,'config_user_id');
    }
}
