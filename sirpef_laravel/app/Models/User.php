<?php
namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'cedula',
        
        'avatar',
        'password',
        'role_id',
      
        'config_user_id', // Añadir este campo
        'deleted_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
        'email_verified_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
        'email_verified_at' => 'datetime',
    ];

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin || $this->role_id === 1;
    }

    public function role()
    {
        return $this->belongsTo(\App\Models\Role::class);
    }

    public function persona()
    {
        return $this->hasOne(Persona::class, 'user_id', 'id');
    }

    public function auditorias()
    {
        return $this->hasMany(Auditoria::class, 'user_id', 'id');
    }

    // Relación inversa con ConfigUser
    public function configUser()
    {
        return $this->belongsTo(ConfigUser::class, 'config_user_id');
    }
}