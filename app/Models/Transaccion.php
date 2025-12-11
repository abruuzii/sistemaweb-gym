<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaccion extends Model
{
    use HasFactory;

    // 👈 MUY IMPORTANTE: nombre real de la tabla
    protected $table = 'transacciones';

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'cliente_id',
        'membresia_id',
        'monto',
        'fecha',
        'tipo',
        'descripcion',
        'tipo_pago',       // 👈 nuevo
        'comprobante_url',  // 👈 nuevo
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function membresia()
    {
        return $this->belongsTo(Membresia::class);
    }
}
