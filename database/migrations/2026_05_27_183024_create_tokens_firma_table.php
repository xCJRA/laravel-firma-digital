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
        Schema::create('tokens_firma', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firmante_id')
                ->constrained('firmantes')
                ->onDelete('cascade');
            $table->string('token', 64)->unique(); // el token que va en el link
            $table->boolean('usado')->default(false);
            $table->timestamp('expira_at'); // 48 horas por defecto
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tokens_firma');
    }
};
