<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    use HasFactory;

    protected $primaryKey = 'placa'; // <--- AÑADE ESTA LÍNEA

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['placa', 'marca', 'color', 'tipo'];
}