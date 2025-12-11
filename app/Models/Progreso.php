<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Progreso extends Model
{
    use HasFactory;

    // Relación con Cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}

