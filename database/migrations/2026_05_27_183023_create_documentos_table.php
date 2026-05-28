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
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('archivo_original')->nullable(); // ruta del PDF subido
            $table->string('archivo_firmado')->nullable();  // ruta del PDF final con firmas
            $table->enum('estado', ['pendiente', 'en_proceso', 'completado', 'rechazado'])
                ->default('pendiente');
            $table->text('descripcion')->nullable();
            $table->timestamps();
            $table->softDeletes(); // deleted_at — nunca borrar documentos legales
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
