<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            // Relasi ke pegawai
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');

            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();  // Bisa null kalau alpa
            $table->time('jam_pulang')->nullable(); // Bisa null kalau lupa absen pulang

            // Status: Hadir, Sakit, Izin, Alpa, Cuti
            $table->string('status_kehadiran')->default('hadir');

            // HASIL HITUNGAN HARIAN (Disimpan biar laporan enteng)
            $table->integer('terlambat_menit')->default(0);
            $table->integer('pulang_cepat_menit')->default(0);

            // Total persen denda hari ini (Misal: 1.50)
            $table->decimal('total_potongan_persen', 5, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
