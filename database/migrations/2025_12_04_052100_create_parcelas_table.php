<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('parcelas')) {
            Schema::create('parcelas', function (Blueprint $table) {
                $table->id('idParcela');
                $table->integer('noParcela');
                $table->string('superficie', 30);
                $table->string('usoSuelo', 30);
                $table->string('ubicacion', 30);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('parcelas');
    }
};
