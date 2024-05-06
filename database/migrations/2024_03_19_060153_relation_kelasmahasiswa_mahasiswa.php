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
        Schema::table('kelas_mahasiswa', function (Blueprint $table) {
            $table->foreign('Mahasiswa_Id')
            ->references('Mahasiswa_Id')->on('mahasiswa')
            ->onDelete('cascade')
            ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kendaraan', function (Blueprint $table) {
            //
        });
    }
};
