<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('registros', function (Blueprint $table) {
        $table->id();
        $table->string('vehiculo_placa', 10);
        $table->foreign('vehiculo_placa')->references('placa')->on('vehiculos');
        $table->foreignId('cliente_id')->constrained('clientes');
        $table->dateTime('hora_ingreso');
        $table->dateTime('hora_salida')->nullable();
        $table->integer('piso');
        $table->integer('espacio');
        $table->decimal('total_pagado', 8, 2)->nullable();
        $table->enum('estado', ['Activo', 'Finalizado'])->default('Activo');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registros');
    }
};
