<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    /**
     * Campos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'usuario_id',
        'nombre',
        'apellido',
        'cedula_identidad',
        'telefono',
        'direccion',
        'correo',
        'fecha_nacimiento',
        'estado',
        'foto',
        'peso',
        'altura',
        'condicion_medica',
        'membresia_id',
    ];

    /**
     * Relación con el usuario (staff) que gestiona al cliente.
     */
    public function usuario()
    {
        // 👇 usamos el modelo User y especificamos el foreign key usuario_id
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Relación con la membresía actual.
     */
    public function membresia()
    {
        return $this->belongsTo(Membresia::class);
    }

    /**
     * Relación con asistencias.
     */
    public function asistencias()
    {
        return $this->hasMany(Asistencia::class);
    }

    /**
     * Relación con transacciones/pagos.
     */
    public function transacciones()
    {
        return $this->hasMany(Transaccion::class);
    }

    /**
     * Relación con progresos.
     */
    public function progresos()
    {
        return $this->hasMany(Progreso::class);
    }

    /**
     * Relación con notificaciones.
     */
    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class);
    }
}
