<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Amplía coordenadaX / coordenadaY de VARCHAR(10) a DECIMAL(10,7)
     * para almacenar coordenadas GPS reales (ej: 19.3850123, -98.1680456).
     *
     * También agrega el campo `orden` para mantener el orden de los vértices
     * del polígono (necesario para dibujar el polígono correctamente en Leaflet).
     */
    public function up(): void
    {
        // 1. Limpiar datos de prueba (VARCHAR(10) no puede tener GPS reales)
        DB::table('coordenada')->truncate();

        Schema::table('coordenada', function (Blueprint $table) {
            // Cambiar a DECIMAL(10,7): soporta valores como 19.3850123 o -98.1680456
            $table->decimal('coordenadaX', 10, 7)->nullable()->change();
            $table->decimal('coordenadaY', 10, 7)->nullable()->change();

            // Orden del vértice dentro del polígono (1, 2, 3, ...)
            if (!Schema::hasColumn('coordenada', 'orden')) {
                $table->unsignedSmallInteger('orden')->default(1)->after('idParcela');
            }
        });
    }

    public function down(): void
    {
        Schema::table('coordenada', function (Blueprint $table) {
            $table->string('coordenadaX', 10)->nullable()->change();
            $table->string('coordenadaY', 10)->nullable()->change();

            if (Schema::hasColumn('coordenada', 'orden')) {
                $table->dropColumn('orden');
            }
        });
    }
};