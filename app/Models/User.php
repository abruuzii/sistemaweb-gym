<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Campos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'usuario',
        'password',
        'rol',
        'imagen_perfil',
        'is_active',
    ];

    /**
     * Campos que deben ocultarse al serializar.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Conversiones automáticas de tipos.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    /**
     * Accesor para nombre completo.
     */
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombre} {$this->apellido}";
    }

    // Ejemplo de relación futura (opcional)
    // public function notificaciones()
    // {
    //     return $this->hasMany(Notificacion::class, 'usuario_id');
    // }
}
