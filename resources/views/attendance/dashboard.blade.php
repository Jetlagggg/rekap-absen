@extends('layouts.app')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@section('subheading', 'Ringkasan kehadiran dan disiplin pegawai · ' . now()->locale('id')->isoFormat('MMMM YYYY'))

@section('actions')
    <a href="{{ route('attendance.create') }}" class="btn btn-primary">
        <i class="bi bi-pencil-square me-1"></i> Input Absensi
    </a>
    <a href="{{ route('attendance.rekap') }}" class="btn btn-outline-secondary">
        <i class="bi bi-file-earmark-text me-1"></i> Lihat Laporan
    </a>
@endsection

@push('styles')
    <style>
        .stat-strip {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            margin-bottom: 22px;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .stat-item {
            padding: 17px 20px;
            border-right: 1px solid var(--line);
        }

        .stat-item:last-child { border-right: 0; }

        .stat-label {
            display: block;
            margin-bottom: 4px;
            color: var(--muted);
            font-size: .69rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .stat-value {
            display: block;
            color: var(--navy);
            font-family: var(--font-display);
            font-size: 2rem;
            font-weight: 600;
            line-height: 1.1;
            font-variant-numeric: tabular-nums;
        }

        .metric-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .metric { padding: 22px; }

        .metric + .metric { border-left: 1px solid var(--line); }

        .metric-value {
            color: var(--navy);
            font-family: var(--font-display);
            font-size: clamp(2.5rem, 5vw, 3.4rem);
            font-weight: 600;
            line-height: 1;
            font-variant-numeric: tabular-nums;
        }

        .metric-label {
            margin-top: 5px;
            color: var(--ink);
            font-size: .92rem;
            font-weight: 600;
        }

        .metric-desc {
            margin: 10px 0 0;
            color: var(--muted);
            font-size: .82rem;
            line-height: 1.5;
        }

        .metric .progress {
            height: 6px;
            margin-top: 18px;
            background: #e9e2d4;
            border-radius: 1px;
        }

        .metric .progress-bar {
            background: var(--navy);
            border-radius: 1px;
        }

        .metric .progress-bar.discipline { background: var(--gold); }

        @media (max-width: 768px) {
            .stat-strip { grid-template-columns: 1fr; }

            .stat-item {
                border-right: 0;
                border-bottom: 1px solid var(--line);
            }

            .stat-item:last-child { border-bottom: 0; }

            .metric-grid { grid-template-columns: 1fr; }

            .metric + .metric {
                border-left: 0;
                border-top: 1px solid var(--line);
            }
        }
    </style>
@endpush

@section('content')
    <div class="stat-strip">
        <div class="stat-item">
            <span class="stat-label">Total Pegawai</span>
            <span class="stat-value">{{ $totalPegawai }}</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Hadir Hari Ini</span>
            <span class="stat-value">{{ $absenHariIni }}</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Telat Hari Ini</span>
            <span class="stat-value">{{ $telatHariIni }}</span>
        </div>
    </div>

    <div class="metric-grid">
        <section class="metric">
            <div class="metric-value">{{ round($persenKehadiran) }}%</div>
            <div class="metric-label">Rata-Rata Kehadiran</div>
            <div class="progress" role="progressbar" aria-label="Rata-rata kehadiran bulan ini" aria-valuenow="{{ round($persenKehadiran) }}" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar" style="width: {{ min(100, max(0, $persenKehadiran)) }}%"></div>
            </div>
            <p class="metric-desc">Persentase kehadiran terhadap hari kerja yang sudah tercatat bulan ini.</p>
        </section>

        <section class="metric">
            <div class="metric-value">{{ round($persenDisiplin) }}%</div>
            <div class="metric-label">Rata-Rata Disiplin</div>
            <div class="progress" role="progressbar" aria-label="Rata-rata disiplin bulan ini" aria-valuenow="{{ round($persenDisiplin) }}" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar discipline" style="width: {{ min(100, max(0, $persenDisiplin)) }}%"></div>
            </div>
            <p class="metric-desc">Persentase pegawai berstatus hadir tanpa potongan bulan ini.</p>
        </section>
    </div>
@endsection
