<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::table('apoyos_sociales', function (Blueprint $table) {
        $table->string('representante_dependencia', 100)
              ->nullable()
              ->after('dependencia')
              ->comment('Nombre del funcionario de la dependencia que entrega el apoyo');

        // Agrega esta línea:
        $table->string('nombre_representante', 100)
              ->nullable()
              ->after('representante_dependencia');
    });
}

public function down(): void
{
    Schema::table('apoyos_sociales', function (Blueprint $table) {
        $table->dropColumn(['representante_dependencia', 'nombre_representante']);
    });
}
};