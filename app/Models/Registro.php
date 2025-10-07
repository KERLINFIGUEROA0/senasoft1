<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registro extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables.
     */
    protected $fillable = [
        'cliente_id',
        'vehiculo_placa',
        'hora_ingreso',
        'piso',
        'espacio',
        'estado',
    ];

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_placa', 'placa');
    }
}