<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    public const STATUS_HADIR = 'hadir';

    public const STATUS_ALPHA = 'alpha';

    public const STATUS_IZIN = 'izin';

    public const STATUS_SAKIT = 'sakit';

    public const STATUS_CUTI = 'cuti';

    public const STATUS_DINAS = 'dinas luar';

    protected $fillable = [
        'employee_id',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'status_kehadiran',
        'terlambat_menit',
        'pulang_cepat_menit',
        'total_potongan_persen',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Fungsi ini otomatis menghitung Rupiah berdasarkan aturan 40%
    public function getNominalPotonganAttribute()
    {
        // 1. Ambil TPP Full Pegawai
        $tppFull = $this->employee->tpp_pagu;

        // 2. Ambil Basis Disiplin (40% dari TPP Full)
        $basisDisiplin = $tppFull * 0.40;

        return ($this->total_potongan_persen / 100) * $basisDisiplin;
    }
}
