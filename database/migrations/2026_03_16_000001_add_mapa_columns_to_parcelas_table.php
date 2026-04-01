<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parcelas', function (Blueprint $table) {
            if (!Schema::hasColumn('parcelas', 'estado')) {
                $table->enum('estado', ['ocupada', 'disponible', 'litigio', 'inactiva'])
                      ->default('disponible');
            }
            if (!Schema::hasColumn('parcelas', 'cultivo')) {
                $table->string('cultivo', 100)->nullable();
            }
            if (!Schema::hasColumn('parcelas', 'tipo_agua')) {
                $table->enum('tipo_agua', ['temporal', 'riego', 'ninguno'])
                      ->default('ninguno');
            }
            if (!Schema::hasColumn('parcelas', 'coordenadas')) {
                $table->json('coordenadas')->nullable();
            }
            if (!Schema::hasColumn('parcelas', 'lat')) {
                $table->decimal('lat', 10, 8)->nullable();
            }
            if (!Schema::hasColumn('parcelas', 'lng')) {
                $table->decimal('lng', 11, 8)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('parcelas', function (Blueprint $table) {
            $table->dropColumn(['estado', 'cultivo', 'tipo_agua', 'coordenadas', 'lat', 'lng']);
        });
    }
};
