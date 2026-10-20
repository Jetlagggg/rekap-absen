<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        $bulanIni = now()->month;
        $tahunIni = now()->year;

        $totalPegawai = Employee::count();

        $absenHariIni = Attendance::whereDate('tanggal', $today)
            ->where('status_kehadiran', Attendance::STATUS_HADIR)
            ->count();

        $telatHariIni = Attendance::whereDate('tanggal', $today)
            ->where('status_kehadiran', Attendance::STATUS_HADIR)
            ->where('terlambat_menit', '>', 0)
            ->count();

        $jumlahHariKerja = Attendance::whereMonth('tanggal', $bulanIni)
            ->whereYear('tanggal', $tahunIni)
            ->distinct('tanggal')
            ->count('tanggal');

        $targetKehadiran = $totalPegawai * ($jumlahHariKerja ?: 1);

        $realisasiKehadiran = Attendance::whereMonth('tanggal', $bulanIni)
            ->whereYear('tanggal', $tahunIni)
            ->where('status_kehadiran', Attendance::STATUS_HADIR)
            ->count();

        $persenKehadiran = $targetKehadiran > 0
            ? ($realisasiKehadiran / $targetKehadiran) * 100
            : 0;

        $totalTepatWaktu = Attendance::whereMonth('tanggal', $bulanIni)
            ->whereYear('tanggal', $tahunIni)
            ->where('status_kehadiran', Attendance::STATUS_HADIR)
            ->where('total_potongan_persen', 0)
            ->count();

        $persenDisiplin = $realisasiKehadiran > 0
            ? ($totalTepatWaktu / $realisasiKehadiran) * 100
            : 0;

        return view('attendance.dashboard', compact(
            'totalPegawai',
            'absenHariIni',
            'telatHariIni',
            'persenKehadiran',
            'persenDisiplin'
        ));
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'nullable|date_format:Y-m-d',
        ], [
            'tanggal.date_format' => 'Format tanggal harus YYYY-MM-DD.',
        ]);

        $tanggal = $validated['tanggal'] ?? now()->toDateString();

        $employees = Employee::leftJoin('attendances', function ($join) use ($tanggal) {
            $join->on('employees.id', '=', 'attendances.employee_id')
                ->where('attendances.tanggal', $tanggal);
        })
            ->select(
                'employees.id as emp_id',
                'employees.nama',
                'employees.jabatan',
                'attendances.jam_masuk',
                'attendances.jam_pulang',
                'attendances.total_potongan_persen',
                'attendances.status_kehadiran'
            )
            ->orderBy('employees.created_at', 'asc')
            ->get();

        return view('attendance.create', compact('employees', 'tanggal'));
    }

    // =========================
    // 3. SIMPAN MASSAL
    // =========================
    public function storeMassal(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'data' => 'required|array',
            'data.*.status' => [
                'required',
                Rule::in([
                    Attendance::STATUS_HADIR,
                    Attendance::STATUS_SAKIT,
                    Attendance::STATUS_IZIN,
                    Attendance::STATUS_CUTI,
                    Attendance::STATUS_DINAS,
                    Attendance::STATUS_ALPHA,
                    'dinas',
                ]),
            ],
            'data.*.masuk' => 'nullable|date_format:H:i',
            'data.*.pulang' => 'nullable|date_format:H:i',
        ], [
            'tanggal.required' => 'Tanggal absensi wajib diisi.',
            'tanggal.date' => 'Tanggal absensi tidak valid.',
            'data.required' => 'Data absensi kosong.',
            'data.*.status.required' => 'Status kehadiran wajib dipilih.',
            'data.*.status.in' => 'Status kehadiran tidak valid.',
            'data.*.masuk.date_format' => 'Format jam masuk harus HH:MM.',
            'data.*.pulang.date_format' => 'Format jam pulang harus HH:MM.',
        ]);

        $tanggal = $validated['tanggal'];
        $data = $validated['data'];

        $employeeIds = Employee::whereIn('id', array_keys($data))
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        $errors = [];

        foreach ($data as $empId => $row) {
            if (! ctype_digit((string) $empId) || ! in_array((string) $empId, $employeeIds, true)) {
                $errors["data.$empId"] = 'Data pegawai tidak ditemukan.';

                continue;
            }

            if ($row['status'] === Attendance::STATUS_HADIR && blank($row['masuk'] ?? null)) {
                $errors["data.$empId.masuk"] = 'Jam masuk wajib diisi untuk status Hadir.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        DB::transaction(function () use ($data, $tanggal) {

            foreach ($data as $empId => $row) {

                $status = $row['status'];
                $jamMasuk = $row['masuk'] ?? null;
                $jamPulang = $row['pulang'] ?? null;

                if ($status === 'dinas') {
                    $status = Attendance::STATUS_DINAS;
                }

                $telat = 0;
                $cepat = 0;
                $persen = 0;

                if ($status === Attendance::STATUS_HADIR) {

                    $hasil = $this->hitungDenda($jamMasuk, $jamPulang, $tanggal);

                    $telat = $hasil['telat'];
                    $cepat = $hasil['cepat'];
                    $persen = $hasil['total_persen'];

                } elseif ($status === Attendance::STATUS_ALPHA) {

                    $persen = 3.0;
                    $jamMasuk = null;
                    $jamPulang = null;

                } elseif ($status === Attendance::STATUS_IZIN) {

                    $persen = 1.75;
                    $jamMasuk = null;
                    $jamPulang = null;

                } else {

                    $persen = 0;
                    $jamMasuk = null;
                    $jamPulang = null;
                }

                Attendance::updateOrCreate(
                    [
                        'employee_id' => $empId,
                        'tanggal' => $tanggal,
                    ],
                    [
                        'status_kehadiran' => $status,
                        'jam_masuk' => $jamMasuk,
                        'jam_pulang' => $jamPulang,
                        'terlambat_menit' => $telat,
                        'pulang_cepat_menit' => $cepat,
                        'total_potongan_persen' => $persen,
                    ]
                );
            }
        });

        return back()->with('success', 'Berhasil menyimpan '.count($data).' baris absensi.');
    }

    public function createEmployee()
    {
        $employees = Employee::orderBy('created_at', 'asc')->get();

        return view('attendance.add-employee', compact('employees'));
    }

    public function storeEmployee(Request $request)
    {
        $request->validate([
            'nip' => 'required|unique:employees,nip',
            'nama' => 'required',
            'jabatan' => 'required',
        ]);

        Employee::create([
            'nip' => $request->nip,
            'nama' => $request->nama,
            'jabatan' => $request->jabatan,
            'tpp_pagu' => 0,
        ]);

        return redirect()->back()->with('success', 'Pegawai berhasil ditambahkan!');
    }

    public function destroyEmployee($id)
    {
        Employee::destroy($id);

        return back()->with('success', 'Pegawai dihapus.');
    }

    public function rekapBulanan(Request $request)
    {
        $bulan = $request->input('bulan', now()->month);
        $tahun = $request->input('tahun', now()->year);

        $employees = $this->generateRekap($bulan, $tahun);

        return view('attendance.rekap', compact('employees', 'bulan', 'tahun'));
    }

    public function downloadPdf(Request $request)
    {
        $bulan = $request->input('bulan', now()->month);
        $tahun = $request->input('tahun', now()->year);

        $employees = $this->generateRekap($bulan, $tahun);

        $pdf = Pdf::loadView('attendance.rekap_pdf', compact('employees', 'bulan', 'tahun'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download("Laporan-Kinerja-$bulan-$tahun.pdf");
    }

    private function generateRekap($bulan, $tahun)
    {
        $employees = Employee::with(['attendances' => function ($query) use ($bulan, $tahun) {
            $query->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun);
        }])->get();

        foreach ($employees as $pegawai) {

            $absen = $pegawai->attendances;

            $pegawai->jml_sakit = $absen->where('status_kehadiran', 'sakit')->count();
            $pegawai->jml_izin = $absen->where('status_kehadiran', 'izin')->count();
            $pegawai->jml_cuti = $absen->where('status_kehadiran', 'cuti')->count();
            $pegawai->jml_alpha = $absen->where('status_kehadiran', 'alpha')->count();

            $pegawai->jml_telat = $absen->where('status_kehadiran', 'hadir')
                ->where('terlambat_menit', '>', 0)
                ->count();

            $dinas = $absen->filter(fn ($a) => in_array(strtolower($a->status_kehadiran), ['dinas luar', 'dinas'])
            )->count();

            $hadir = $absen->where('status_kehadiran', 'hadir')->count();

            $pegawai->dl = $dinas;
            $pegawai->total_hadir = $hadir + $dinas;

            $totalPersen = min($absen->sum('total_potongan_persen'), 100);

            $pegawai->rekap_persen = $totalPersen;
            $pegawai->skor_kinerja = 100 - $totalPersen;
        }

        return $employees;
    }

    private function hitungDenda($jamMasuk, $jamPulang, $tanggal)
    {
        $isJumat = Carbon::parse($tanggal)->isFriday();
        $jamPulangStandar = $isJumat ? '11:00:00' : '16:00:00';

        $jamMasukResmi = Carbon::parse("$tanggal 08:00:00");
        $jamBatasToleransi = Carbon::parse("$tanggal 08:10:00");
        $jamPulangKantor = Carbon::parse("$tanggal $jamPulangStandar");

        $waktuMasuk = Carbon::parse("$tanggal $jamMasuk");
        $waktuPulang = $jamPulang ? Carbon::parse("$tanggal $jamPulang") : null;

        $totalPotongan = 0;
        $telatMenit = 0;
        $cepatMenit = 0;

        if ($waktuMasuk->gt($jamBatasToleransi)) {

            $telatMenit = $jamMasukResmi->diffInMinutes($waktuMasuk);

            if ($telatMenit <= 30) {
                $totalPotongan += 0.5;
            } elseif ($telatMenit <= 60) {
                $totalPotongan += 1.0;
            } elseif ($telatMenit <= 90) {
                $totalPotongan += 1.25;
            } else {
                $totalPotongan += 1.5;
            }
        }

        if ($waktuPulang) {

            if ($waktuPulang->lt($jamPulangKantor)) {
                $cepatMenit = $waktuPulang->diffInMinutes($jamPulangKantor);
                $totalPotongan += 1.0;
            }

        } elseif ($jamMasuk) {

            $totalPotongan += 1.5;
        }

        return [
            'telat' => $telatMenit,
            'cepat' => $cepatMenit,
            'total_persen' => $totalPotongan,
        ];
    }

    public function updateEmployee(Request $request, $id)
    {
        $request->validate([
            'nip' => 'required',
            'nama' => 'required',
            'jabatan' => 'required',
        ]);

        $employee = Employee::findOrFail($id);

        $employee->update([
            'nip' => $request->nip,
            'nama' => $request->nama,
            'jabatan' => $request->jabatan,
        ]);

        return redirect()->back()->with('success', 'Data pegawai berhasil diperbarui!');
    }

    public function importEmployee(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt',
        ]);

        $file = $request->file('file');

        $rows = array_map('str_getcsv', file($file->getRealPath()));

        // hapus header
        unset($rows[0]);

        $insertData = [];

        foreach ($rows as $row) {

            // skip kalau kolom kurang
            if (count($row) < 3) {
                continue;
            }

            $insertData[] = [
                'nip' => $row[0],
                'nama' => $row[1],
                'jabatan' => $row[2],
                'tpp_pagu' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (! empty($insertData)) {
            Employee::insert($insertData);
        }

        return back()->with('success', 'Import pegawai berhasil.');
    }
}
