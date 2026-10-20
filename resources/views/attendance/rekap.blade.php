@extends('layouts.app')

@section('title', 'Laporan Kinerja')
@section('heading', 'Laporan Kinerja')
@section('subheading')
    Rekap disiplin dan potongan TPP periode {{ \Carbon\Carbon::create(null, $bulan, 1)->locale('id')->isoFormat('MMMM YYYY') }}.
@endsection

@section('actions')
    <form action="{{ route('attendance.rekap') }}" method="GET" class="filter-group">
        <label for="bulan" class="visually-hidden">Bulan laporan</label>
        <select id="bulan" name="bulan" class="form-select form-select-sm" onchange="this.form.submit()">
            @for($i = 1; $i <= 12; $i++)
                <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create(null, $i, 1)->locale('id')->isoFormat('MMMM') }}
                </option>
            @endfor
        </select>

        <label for="tahun" class="visually-hidden">Tahun laporan</label>
        <select id="tahun" name="tahun" class="form-select form-select-sm" onchange="this.form.submit()">
            @for($y = 2025; $y <= 2099; $y++)
                <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>

        <a href="{{ route('attendance.downloadPdf', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-outline-secondary">
            <i class="bi bi-file-earmark-pdf me-1"></i> PDF
        </a>
    </form>
@endsection

@push('styles')
    <style>
        .filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .filter-group .form-select {
            width: auto;
            min-width: 122px;
            border-color: var(--line-strong);
            font-weight: 600;
        }

        .record-count {
            padding: 4px 9px;
            color: var(--muted);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            font-size: .76rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .table-custom { min-width: 980px; }

        .table-custom th {
            text-align: center;
            vertical-align: middle;
        }

        .table-custom td { vertical-align: middle; }

        .table-custom tbody td {
            font-variant-numeric: tabular-nums;
        }

        .employee-name {
            color: var(--ink);
            font-weight: 600;
        }

        .employee-role {
            color: var(--muted);
            font-size: .78rem;
        }

        .score {
            font-family: var(--font-display);
            font-size: 1.15rem;
            font-weight: 600;
            font-variant-numeric: tabular-nums;
        }

        .score.is-low { color: var(--danger); }
        .score.is-good { color: var(--success); }

        .penalty-badge {
            display: inline-block;
            padding: 4px 9px;
            color: var(--danger);
            background: #f8ecec;
            border: 1px solid #e6c9c9;
            border-radius: var(--radius);
            font-size: .8rem;
            font-weight: 600;
            font-variant-numeric: tabular-nums;
        }

        .table-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 6px 18px;
            padding: 12px 18px;
            border-top: 1px solid var(--line);
            color: var(--muted);
            font-size: .76rem;
        }

        .table-legend strong {
            color: var(--ink);
            font-weight: 700;
        }

        .dinas-column { background: #f4f9fa; }

        .period-note {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 16px;
            padding: 12px 14px;
            color: #4d4229;
            background: #fbf5e7;
            border: 1px solid #e2d2ad;
            border-radius: var(--radius);
            font-size: .88rem;
        }

        .period-note i {
            color: var(--gold);
            font-size: 1rem;
            line-height: 1.4;
        }

        .period-note p { margin: 0; }

        .period-note a {
            color: var(--navy);
            font-weight: 600;
            text-decoration: underline;
            text-underline-offset: 2px;
        }

        @media (max-width: 640px) {
            .filter-group { width: 100%; }
            .filter-group .form-select { flex: 1; min-width: 0; }
        }
    </style>
@endpush

@section('content')
    @if($employees->isNotEmpty() && $employees->every(fn ($e) => $e->attendances->isEmpty()))
        <div class="period-note" role="status">
            <i class="bi bi-info-circle-fill" aria-hidden="true"></i>
            <p>
                <strong>Belum ada absensi pada periode ini.</strong>
                Skor disiplin baru dihitung setelah absensi diinput.
                <a href="{{ route('attendance.create') }}">Input absensi</a>.
            </p>
        </div>
    @endif

    <div class="panel">
        <div class="panel-header">
            <div>
                <h2 class="panel-title">Rincian per Pegawai</h2>
                <p class="panel-subtitle">Hadir mencakup Dinas Luar. Tanda &ldquo;-&rdquo; berarti tidak ada.</p>
            </div>
            <span class="record-count">{{ $employees->count() }} pegawai</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-custom mb-0">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 5%;">No</th>
                        <th rowspan="2" class="text-start ps-3">Nama Pegawai</th>
                        <th rowspan="2" style="width: 9%;">Jumlah<br>Hadir</th>
                        <th colspan="6">Rincian (Hari)</th>
                        <th rowspan="2" style="width: 13%;">Total<br>Potongan</th>
                        <th rowspan="2" style="width: 14%;">Penilaian<br>Disiplin</th>
                    </tr>
                    <tr>
                        <th width="6%" title="Telat">TLT</th>
                        <th width="6%" title="Sakit">SKT</th>
                        <th width="6%" title="Izin">IZN</th>
                        <th width="6%" title="Cuti">CT</th>
                        <th width="6%" title="Tanpa Keterangan">TK</th>
                        <th width="6%" class="dinas-column" title="Dinas Luar">DL</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $index => $e)
                    <tr>
                        <td class="text-center text-muted">{{ $index + 1 }}</td>
                        <td class="ps-3">
                            <div class="employee-name">{{ $e->nama }}</div>
                            <div class="employee-role">{{ $e->jabatan }}</div>
                        </td>

                        <td class="text-center fw-semibold">
                            {{ $e->total_hadir }} Hari
                        </td>

                        <td class="text-center fw-semibold {{ $e->jml_telat > 0 ? 'text-danger' : 'text-muted' }}">
                            {{ $e->jml_telat > 0 ? $e->jml_telat : '-' }}
                        </td>
                        <td class="text-center fw-semibold {{ $e->jml_sakit > 0 ? 'text-primary' : 'text-muted' }}">
                            {{ $e->jml_sakit > 0 ? $e->jml_sakit : '-' }}
                        </td>
                        <td class="text-center fw-semibold {{ $e->jml_izin > 0 ? 'text-success' : 'text-muted' }}">
                            {{ $e->jml_izin > 0 ? $e->jml_izin : '-' }}
                        </td>
                        <td class="text-center fw-semibold {{ $e->jml_cuti > 0 ? 'text-secondary' : 'text-muted' }}">
                            {{ $e->jml_cuti > 0 ? $e->jml_cuti : '-' }}
                        </td>
                        <td class="text-center fw-semibold {{ $e->jml_alpha > 0 ? 'text-danger' : 'text-muted' }}">
                            {{ $e->jml_alpha > 0 ? $e->jml_alpha : '-' }}
                        </td>
                        <td class="text-center fw-semibold dinas-column {{ $e->dl > 0 ? 'text-info' : 'text-muted' }}">
                            {{ $e->dl > 0 ? $e->dl : '-' }}
                        </td>

                        <td class="text-center">
                            @if($e->attendances->isEmpty())
                                <span class="text-muted" title="Belum ada absensi pada periode ini">&mdash;</span>
                            @elseif($e->rekap_persen > 0)
                                <span class="penalty-badge">-{{ $e->rekap_persen }}%</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td class="text-center">
                            @if($e->attendances->isEmpty())
                                <span class="text-muted" title="Belum ada absensi pada periode ini">&mdash;</span>
                            @else
                                <span class="score {{ $e->skor_kinerja < 90 ? 'is-low' : 'is-good' }}">{{ $e->skor_kinerja }}%</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-5">
                            <div class="text-muted mb-2">Belum ada pegawai terdaftar.</div>
                            <a href="{{ route('employee.create') }}" class="btn btn-outline-secondary btn-sm">Tambah Pegawai</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-legend">
            <span><strong>TLT</strong> Telat</span>
            <span><strong>SKT</strong> Sakit</span>
            <span><strong>IZN</strong> Izin</span>
            <span><strong>CT</strong> Cuti</span>
            <span><strong>TK</strong> Tanpa Keterangan</span>
            <span><strong>DL</strong> Dinas Luar</span>
        </div>
    </div>
@endsection
