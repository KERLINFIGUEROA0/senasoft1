<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Creamos un único usuario para nuestra API
        User::create([
            'name' => 'Parqueadero App',
            'email' => 'api-user@parqueadero.com', // Puedes cambiar este email
            'password' => Hash::make('una-contraseña-muy-segura') // CAMBIA ESTA CONTRASEÑA
        ]);
    }
}