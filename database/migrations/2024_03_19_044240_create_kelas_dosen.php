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
        Schema::create('kelas_dosen', function (Blueprint $table) {
            $table->integer('Kelas_Dosen_Id')->autoIncrement();
            $table->unsignedBigInteger('Dosen_Id');
            $table->unsignedBigInteger('Kelas_Id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas_dosen');
    }
};
