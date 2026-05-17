<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parcelas', function (Blueprint $table) {
            $table->string('superficie')->nullable()->change();
            $table->string('ubicacion')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('parcelas', function (Blueprint $table) {
            $table->string('superficie')->nullable(false)->change();
            $table->string('ubicacion')->nullable(false)->change();
        });
    }
};
