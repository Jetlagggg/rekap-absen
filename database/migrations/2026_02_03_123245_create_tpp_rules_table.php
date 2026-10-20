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
        Schema::create('tpp_rules', function (Blueprint $table) {
            $table->id();
            // Jenis: 'terlambat', 'pulang_cepat'
            $table->string('jenis');

            // Keterangan: "Terlambat 1-30 Menit"
            $table->string('keterangan');

            // Range Menit (1 s.d 30)
            $table->integer('min_menit')->default(0);
            $table->integer('max_menit')->nullable(); // Nullable buat yang "> 90 menit"

            // Persen Potongan (0.5, 1.0, 1.25)
            // Pakai decimal biar presisi, jangan float/integer
            $table->decimal('potongan', 5, 2);

            $table->timestamps();
        });
    }
};
