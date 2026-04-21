<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    if (!Schema::hasTable('coordenadas')) {
        Schema::create('coordenadas', function (Blueprint $table) {
            $table->id('idCoordenada');
            $table->unsignedBigInteger('idParcela');
            $table->string('punto', 10);
            $table->string('coordenadaX', 50);
            $table->string('coordenadaY', 50);
        });
    }
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coordenadas');
    }
};
