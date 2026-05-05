<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Hacer nullable las colindancias diagonales
        Schema::table('colindancia', function (Blueprint $table) {
            $table->string('noreste',  100)->nullable()->change();
            $table->string('noroeste', 100)->nullable()->change();
            $table->string('sureste',  100)->nullable()->change();
            $table->string('suroeste', 100)->nullable()->change();
            // También las principales por si acaso
            $table->string('norte', 100)->nullable()->change();
            $table->string('sur',   100)->nullable()->change();
            $table->string('este',  100)->nullable()->change();
            $table->string('oeste', 100)->nullable()->change();
        });

        // 2. Ampliar precisión de coordenadas a 25 dígitos significativos
        // DECIMAL(25,15) soporta: -9999999999.123456789012345
        // Más que suficiente para coordenadas GPS de alta precisión
        Schema::table('coordenada', function (Blueprint $table) {
            $table->decimal('coordenadaX', 25, 15)->nullable()->change();
            $table->decimal('coordenadaY', 25, 15)->nullable()->change();
        });

        // 3. Ampliar también lat/lng del centroide en parcelas
        Schema::table('parcelas', function (Blueprint $table) {
            $table->decimal('lat', 25, 15)->nullable()->change();
            $table->decimal('lng', 25, 15)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('colindancia', function (Blueprint $table) {
            $table->string('noreste',  100)->nullable(false)->change();
            $table->string('noroeste', 100)->nullable(false)->change();
            $table->string('sureste',  100)->nullable(false)->change();
            $table->string('suroeste', 100)->nullable(false)->change();
        });

        Schema::table('coordenada', function (Blueprint $table) {
            $table->decimal('coordenadaX', 10, 7)->nullable()->change();
            $table->decimal('coordenadaY', 10, 7)->nullable()->change();
        });

        Schema::table('parcelas', function (Blueprint $table) {
            $table->decimal('lat', 10, 7)->nullable()->change();
            $table->decimal('lng', 10, 7)->nullable()->change();
        });
    }
};