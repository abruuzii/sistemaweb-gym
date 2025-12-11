<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'juan@example.com',
            'password' => bcrypt('password123'),
            'usuario' => 'juanperez',
            'rol' => 'admin',
        ]);

        User::create([
            'nombre' => 'María',
            'apellido' => 'Gómez',
            'email' => 'maria@example.com',
            'password' => bcrypt('password123'),
            'usuario' => 'mariagomez',
            'rol' => 'entrenador',
        ]);

        User::create([
            'nombre' => 'Carlos',
            'apellido' => 'López',
            'email' => 'carlos@example.com',
            'password' => bcrypt('password123'),
            'usuario' => 'carloslopez',
            'rol' => 'recepcionista',
        ]);
    }
}

