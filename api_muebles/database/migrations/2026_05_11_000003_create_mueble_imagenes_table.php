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
        Schema::create('mueble_imagenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mueble_id')->constrained('muebles')->onDelete('cascade');
            $table->string('url');
            $table->integer('orden')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mueble_imagenes');
    }
};
