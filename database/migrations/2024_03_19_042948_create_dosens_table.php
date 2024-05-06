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
        Schema::create('dosen', function (Blueprint $table) {
            $table->unsignedBigInteger("Dosen_Id")->autoIncrement();
            $table->string("Dosen_Nama");
            $table->string("Dosen_Email");
            $table->string("Dosen_Password");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dosens');
    }
};
