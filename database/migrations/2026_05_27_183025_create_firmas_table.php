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
        Schema::create('firmas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firmante_id')
                ->constrained('firmantes')
                ->onDelete('cascade');
            $table->foreignId('documento_id')
                ->constrained('documentos')
                ->onDelete('cascade');
            $table->string('ip_address', 45); // IPv4 e IPv6
            $table->timestamp('firmado_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('firmas');
    }
};
