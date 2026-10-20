@extends('layouts.app')

@section('title', 'Input Absensi')
@section('heading', 'Input Absensi')
@section('subheading')
    Catat kehadiran pegawai untuk tanggal {{ \Carbon\Carbon::parse($tanggal)->locale('id')->isoFormat('D MMMM YYYY') }}.
@endsection

@section('actions')
    <form action="{{ route('attendance.create') }}" method="GET" class="date-control">
        <i class="bi bi-calendar3" aria-hidden="true"></i>
        <label for="tanggalAbsen" class="visually-hidden">Tanggal absensi</label>
        <input type="date" id="tanggalAbsen" name="tanggal" value="{{ $tanggal }}" onchange="this.form.submit()">
    </form>

    <div class="search-control">
        <i class="bi bi-search" aria-hidden="true"></i>
        <label for="searchAbsen" class="visually-hidden">Cari nama pegawai</label>
        <input type="text" id="searchAbsen" placeholder="Cari nama pegawai...">
    </div>

    <a href="{{ route('employee.create') }}" class="btn btn-outline-secondary">
        <i class="bi bi-person-plus me-1"></i> Pegawai
    </a>
@endsection

@push('styles')
    <style>
        .date-control,
        .search-control {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 4px 10px;
            background: var(--surface);
            border: 1px solid var(--line-strong);
            border-radius: var(--radius);
        }

        .date-control i,
        .search-control i {
            color: var(--muted);
            font-size: .9rem;
        }

        .date-control input,
        .search-control input {
            padding: 4px 2px;
            border: 0;
            background: transparent;
            color: var(--ink);
            font-size: .88rem;
        }

        .date-control input:focus,
        .search-control input:focus { outline: none; }

        .search-control input { min-width: 190px; }

        .excel-table { min-width: 760px; }

        .excel-table thead th { white-space: nowrap; }

        .cell-input {
            width: 100%;
            padding: .58rem .25rem;
            border: 0;
            background: transparent;
            color: var(--ink);
            text-align: center;
            font-weight: 600;
            font-variant-numeric: tabular-nums;
        }

        .cell-input:focus {
            outline: 2px solid var(--gold);
            outline-offset: -2px;
            background: #fbf8f1;
        }

        .cell-input:disabled {
            color: var(--muted);
            opacity: .45;
        }

        .select-status {
            min-width: 126px;
            border-color: var(--line-strong);
            font-size: .82rem;
        }

        .status-hadir { background: #eaf2ec; color: #24513c; }
        .status-alpha { background: #f7e7e7; color: #7d2222; }
        .status-sakit { background: #e9eff5; color: #274b6b; }
        .status-izin { background: #f5edda; color: #6b5224; }
        .status-cuti { background: #f0ece3; color: #5a5346; }
        .status-dinas,
        .status-dinas-luar { background: #e8f0f2; color: #23525c; }

        .potongan-cell {
            background: #faf7ef;
            font-size: .9rem;
        }

        .save-bar {
            position: sticky;
            bottom: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 16px;
            padding: 12px 16px;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            box-shadow: 0 -6px 18px rgba(23, 50, 77, .06);
        }

        .save-note {
            color: var(--muted);
            font-size: .82rem;
        }

        @media (max-width: 640px) {
            .date-control,
            .search-control { width: 100%; }

            .search-control input {
                flex: 1;
                min-width: 0;
            }

            .save-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .save-note { display: none; }
        }
    </style>
@endpush

@section('content')
    <form action="{{ route('attendance.storeMassal') }}" method="POST">
        @csrf
        <input type="hidden" name="tanggal" value="{{ $tanggal }}">

        <div class="panel">
            <div class="table-responsive">
                <table class="table table-hover align-middle excel-table">
                    <thead>
                        <tr>
                            <th style="width: 48px;">No</th>
                            <th style="text-align: left; padding-left: 15px;">Nama Pegawai</th>
                            <th style="width: 150px;">Keterangan</th>
                            <th style="width: 130px;">Masuk</th>
                            <th style="width: 130px;">Pulang</th>
                            <th style="width: 110px;">Potongan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $index => $emp)
                        @php
                            $statusSekarang = old('data.'.$emp->emp_id.'.status', $emp->status_kehadiran ?? 'hadir');
                            $kelasStatus = in_array($statusSekarang, ['dinas', 'dinas luar'], true) ? 'dinas' : $statusSekarang;
                            $masukSekarang = old('data.'.$emp->emp_id.'.masuk', $emp->jam_masuk ? date('H:i', strtotime($emp->jam_masuk)) : '');
                            $pulangSekarang = old('data.'.$emp->emp_id.'.pulang', $emp->jam_pulang ? date('H:i', strtotime($emp->jam_pulang)) : '');
                        @endphp
                        <tr id="row-{{ $emp->emp_id }}" class="absen-row">
                            <td class="text-center font-monospace text-muted">{{ $index + 1 }}</td>
                            <td style="padding-left: 15px;">
                                <div class="emp-name fw-semibold">{{ $emp->nama }}</div>
                                <small class="text-muted text-uppercase" style="font-size: 10.5px; letter-spacing: .04em;">{{ $emp->jabatan }}</small>
                            </td>
                            <td class="text-center">
                                <select name="data[{{ $emp->emp_id }}][status]" class="form-select form-select-sm select-status status-{{ $kelasStatus }}" data-row-id="{{ $emp->emp_id }}" onchange="gantiStatus(this)">
                                    <option value="hadir" {{ $statusSekarang == 'hadir' ? 'selected' : '' }}>Hadir</option>
                                    <option value="sakit" {{ $statusSekarang == 'sakit' ? 'selected' : '' }}>Sakit</option>
                                    <option value="izin" {{ $statusSekarang == 'izin' ? 'selected' : '' }}>Izin</option>
                                    <option value="cuti" {{ $statusSekarang == 'cuti' ? 'selected' : '' }}>Cuti</option>
                                    <option value="dinas luar" {{ in_array($statusSekarang, ['dinas', 'dinas luar'], true) ? 'selected' : '' }}>Dinas Luar</option>
                                    <option value="alpha" {{ $statusSekarang == 'alpha' ? 'selected' : '' }}>TK (Alpha)</option>
                                </select>
                                @error('data.'.$emp->emp_id.'.status')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </td>
                            <td>
                                <input type="text" name="data[{{ $emp->emp_id }}][masuk]" id="masuk-{{ $emp->emp_id }}" class="cell-input input-masuk" data-row-id="{{ $emp->emp_id }}" value="{{ $masukSekarang }}" placeholder="--:--" {{ $statusSekarang !== 'hadir' ? 'disabled' : '' }}>
                                @error('data.'.$emp->emp_id.'.masuk')
                                    <div class="text-danger small text-center">{{ $message }}</div>
                                @enderror
                            </td>
                            <td>
                                <input type="text" name="data[{{ $emp->emp_id }}][pulang]" id="pulang-{{ $emp->emp_id }}" class="cell-input input-pulang" data-row-id="{{ $emp->emp_id }}" value="{{ $pulangSekarang }}" placeholder="--:--" {{ $statusSekarang !== 'hadir' ? 'disabled' : '' }}>
                                @error('data.'.$emp->emp_id.'.pulang')
                                    <div class="text-danger small text-center">{{ $message }}</div>
                                @enderror
                            </td>
                            <td class="text-center potongan-cell" id="potongan-{{ $emp->emp_id }}">
                                @if($statusSekarang === 'alpha')
                                    <span class="text-danger">3%</span>
                                @elseif($statusSekarang === 'izin')
                                    <span class="text-danger">1.75%</span>
                                @elseif($statusSekarang !== 'hadir')
                                    <span class="text-success">0%</span>
                                @elseif(isset($emp->total_potongan_persen))
                                    @if($emp->total_potongan_persen > 0)
                                        <span class="text-danger">{{ $emp->total_potongan_persen }}%</span>
                                    @else
                                        <span class="text-success">0%</span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted mb-2">Belum ada pegawai terdaftar.</div>
                                <a href="{{ route('employee.create') }}" class="btn btn-outline-secondary btn-sm">Tambah Pegawai</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="save-bar">
            <div class="save-note"><i class="bi bi-info-circle me-1"></i> Periksa kembali jam dan status sebelum menyimpan.</div>
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-save me-2"></i> Simpan Perubahan
            </button>
        </div>
    </form>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr(".cell-input", { enableTime: true, noCalendar: true, dateFormat: "H:i", time_24hr: true, allowInput: true });

        document.getElementById('searchAbsen').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            document.querySelectorAll('.absen-row').forEach(row => {
                row.style.display = row.querySelector('.emp-name').innerText.toLowerCase().includes(filter) ? '' : 'none';
            });
        });

        const tanggalAbsen = @json($tanggal);
        const batasPulangMenit = new Date(tanggalAbsen + 'T00:00:00').getDay() === 5 ? 11 * 60 : 16 * 60;

        function keMenit(jam) {
            const [jamBagian, menitBagian] = jam.split(':').map(Number);
            return (jamBagian * 60) + menitBagian;
        }

        function formatPersen(nilai) {
            if (Number.isInteger(nilai)) return nilai + '%';
            return nilai.toFixed(2).replace(/0+$/, '').replace(/\.$/, '') + '%';
        }

        function kosongkan(input) {
            if (input._flatpickr) {
                input._flatpickr.clear();
            } else {
                input.value = '';
            }
        }

        function hitungPotonganPreview(rowId) {
            const select = document.querySelector('.select-status[data-row-id="' + rowId + '"]');
            const pot = document.getElementById('potongan-' + rowId);
            if (!select || !pot) return;

            const status = select.value;

            if (status === 'alpha') {
                pot.innerHTML = "<span class='text-danger fw-bold'>3%</span>";
                return;
            }

            if (status === 'izin') {
                pot.innerHTML = "<span class='text-danger fw-bold'>1.75%</span>";
                return;
            }

            if (status !== 'hadir') {
                pot.innerHTML = "<span class='text-success fw-bold'>0%</span>";
                return;
            }

            const masuk = document.getElementById('masuk-' + rowId).value;
            const pulang = document.getElementById('pulang-' + rowId).value;

            if (!masuk) {
                pot.innerHTML = "<span class='text-muted small fst-italic'>Isi jam masuk</span>";
                return;
            }

            let total = 0;

            if (keMenit(masuk) > 8 * 60 + 10) {
                const telat = keMenit(masuk) - 8 * 60;
                if (telat <= 30) total += 0.5;
                else if (telat <= 60) total += 1;
                else if (telat <= 90) total += 1.25;
                else total += 1.5;
            }

            if (pulang) {
                if (keMenit(pulang) < batasPulangMenit) total += 1;
            } else {
                total += 1.5;
            }

            pot.innerHTML = "<span class='" + (total > 0 ? 'text-danger' : 'text-success') + " fw-bold'>" + formatPersen(total) + "</span>";
        }

        function gantiStatus(select) {
            const rowId = select.dataset.rowId;
            const val = select.value;
            const m = document.getElementById('masuk-' + rowId);
            const p = document.getElementById('pulang-' + rowId);

            select.className = 'form-select form-select-sm select-status status-' + val.replace(/\s+/g, '-');

            if (val === 'hadir') {
                m.disabled = false;
                p.disabled = false;
            } else {
                m.disabled = true;
                p.disabled = true;
                kosongkan(m);
                kosongkan(p);
            }

            hitungPotonganPreview(rowId);
        }

        function updateRow(input) {
            if (input.classList.contains('input-masuk') && input.value) {
                input.style.color = keMenit(input.value) > 8 * 60 + 10 ? '#8f2d2d' : '#2f634a';
                input.style.fontWeight = 'bold';
            } else if (input.classList.contains('input-masuk')) {
                input.style.color = '';
                input.style.fontWeight = '';
            }

            hitungPotonganPreview(input.dataset.rowId);
        }

        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('input-masuk') || e.target.classList.contains('input-pulang')) updateRow(e.target);
        });
        document.addEventListener('input', (e) => {
            if (e.target.classList.contains('input-masuk') || e.target.classList.contains('input-pulang')) updateRow(e.target);
        });

        document.querySelectorAll('.absen-row').forEach(row => {
            const masuk = row.querySelector('.input-masuk');
            if (masuk && masuk.value && !masuk.disabled) updateRow(masuk);
        });
    </script>
@endpush
