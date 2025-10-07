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
    Schema::create('vehiculos', function (Blueprint $table) {
        $table->string('placa', 10)->primary(); // La placa es la llave primaria
        $table->string('marca')->nullable();
        $table->string('color')->nullable();
        $table->enum('tipo', ['Motocicleta', 'Carro']);
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};
