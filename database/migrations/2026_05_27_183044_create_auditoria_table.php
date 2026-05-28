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
        Schema::create('auditoria', function (Blueprint $table) {
            $table->id();
            $table->string('accion');          // "documento_creado", "firma_registrada", etc.
            $table->string('entidad');         // "Documento", "Firma", etc.
            $table->unsignedBigInteger('entidad_id');
            $table->json('datos')->nullable(); // snapshot de los datos en ese momento
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auditoria');
    }
};
