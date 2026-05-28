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
        Schema::create('firmantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documento_id')
                ->constrained('documentos')
                ->onDelete('cascade');
            $table->string('nombre');
            $table->string('email');
            $table->unsignedTinyInteger('orden'); // 1, 2, 3 — el orden de firma
            $table->enum('estado', ['pendiente', 'firmado', 'rechazado'])
                ->default('pendiente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('firmantes');
    }
};
