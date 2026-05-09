<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('apoyos_sociales', function (Blueprint $table) {
        $table->increments('idApoyo');
        $table->integer('idEjidatario');         // ← int normal, igual que ejidatarios
        $table->string('tipo_apoyo', 100);
        $table->string('descripcion', 255)->nullable();
        $table->decimal('monto', 10, 2)->default(0);
        $table->integer('cantidad')->default(0);
        $table->string('unidad_medida', 30)->nullable();
        $table->date('fecha_entrega');
        $table->string('ciclo', 20)->nullable();
        $table->string('dependencia', 100)->nullable();
        $table->string('nombre_representante', 100);
        $table->integer('num_beneficiarios')->default(1);
        $table->enum('estatus', ['entregado', 'pendiente', 'cancelado'])
              ->default('pendiente');
        $table->text('observaciones')->nullable();

        $table->foreign('idEjidatario', 'fk_apoyo_ejidatario')
              ->references('idEjidatario')
              ->on('ejidatarios');
    });
}

    public function down(): void
    {
        Schema::dropIfExists('apoyos_sociales');
    }
};