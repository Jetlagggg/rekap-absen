@extends('layouts.app')

@section('title', 'Kelola Pegawai')
@section('heading', 'Kelola Pegawai')
@section('subheading')
    Tambah, ubah, dan cari data pegawai &middot; {{ $employees->count() }} pegawai terdaftar.
@endsection

@section('actions')
    <a href="{{ route('attendance.create') }}" class="btn btn-outline-secondary">
        <i class="bi bi-pencil-square me-1"></i> Input Absensi
    </a>
@endsection

@push('styles')
    <style>
        .employee-layout {
            display: grid;
            grid-template-columns: minmax(300px, 380px) minmax(0, 1fr);
            gap: 18px;
            align-items: start;
        }

        .form-stack {
            display: grid;
            gap: 18px;
            min-width: 0;
        }

        .employee-layout > * { min-width: 0; }

        .search-control {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 4px 10px;
            background: #fffefb;
            border: 1px solid var(--line-strong);
            border-radius: var(--radius);
        }

        .search-control i {
            color: var(--muted);
            font-size: .9rem;
        }

        .search-control input {
            width: 190px;
            padding: 4px 2px;
            border: 0;
            background: transparent;
            color: var(--ink);
            font-size: .86rem;
        }

        .search-control input:focus { outline: none; }

        .list-header {
            flex-wrap: wrap;
        }

        .employee-table { min-width: 640px; }

        .employee-name {
            color: var(--ink);
            font-weight: 600;
        }

        .employee-nip {
            color: var(--muted);
            font-size: .75rem;
        }

        .role-pill {
            display: inline-block;
            padding: 3px 8px;
            color: var(--muted);
            background: #faf7ef;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            font-size: .76rem;
        }

        .btn-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            padding: 0;
            color: var(--muted);
            background: transparent;
            border: 1px solid transparent;
            border-radius: var(--radius);
        }

        .btn-icon:hover,
        .btn-icon:focus {
            color: var(--navy);
            background: var(--gold-soft);
            border-color: var(--line);
        }

        .btn-icon.is-danger:hover,
        .btn-icon.is-danger:focus {
            color: var(--danger);
            background: #f8ecec;
            border-color: #e6c9c9;
        }

        .modal-content {
            border: 1px solid var(--line);
            border-radius: var(--radius);
        }

        .modal-header {
            background: #f1ede3;
            border-bottom: 1px solid var(--line);
        }

        .modal-title {
            color: var(--navy);
            font-family: var(--font-display);
            font-size: 1.08rem;
        }

        .modal-footer { border-top: 1px solid var(--line); }

        @media (max-width: 992px) {
            .employee-layout { grid-template-columns: 1fr; }
        }

        @media (max-width: 640px) {
            .search-control { width: 100%; }
            .search-control input { flex: 1; width: auto; min-width: 0; }
        }
    </style>
@endpush

@section('content')
    <div class="employee-layout">
        <div class="form-stack">
            <section class="panel">
                <div class="panel-header">
                    <div>
                        <h2 class="panel-title">Tambah Pegawai</h2>
                        <p class="panel-subtitle">NIP/NIPPPK wajib unik.</p>
                    </div>
                </div>
                <div class="panel-body">
                    <form action="{{ route('employee.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="nip" class="form-label">NIP/NIPPPK</label>
                            <input type="text" id="nip" name="nip" class="form-control input-nip" placeholder="NIP/NIPPPK" required maxlength="21" autocomplete="off">
                            @error('nip')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap</label>
                            <input type="text" id="nama" name="nama" class="form-control" placeholder="Nama lengkap & gelar" required autocomplete="off">
                            @error('nama')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="jabatan" class="form-label">Jabatan</label>
                            <input type="text" id="jabatan" name="jabatan" class="form-control" placeholder="Jabatan struktural / fungsional" required autocomplete="off">
                            @error('jabatan')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary py-2">
                                <i class="bi bi-person-plus me-1"></i> Simpan Pegawai
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            <section class="panel">
                <div class="panel-header">
                    <div>
                        <h2 class="panel-title">Import CSV</h2>
                        <p class="panel-subtitle">Format: nip,nama,jabatan</p>
                    </div>
                </div>
                <div class="panel-body">
                    <form action="{{ route('pegawai.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="csvFile" class="form-label">File CSV</label>
                            <input type="file" id="csvFile" name="file" class="form-control" accept=".csv" required>
                            @error('file')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-outline-success py-2">
                                <i class="bi bi-upload me-1"></i> Import CSV
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </div>

        <section class="panel">
            <div class="panel-header list-header">
                <div>
                    <h2 class="panel-title">Daftar Pegawai</h2>
                    <p class="panel-subtitle">{{ $employees->count() }} pegawai</p>
                </div>
                <div class="search-control">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <label for="searchInput" class="visually-hidden">Cari nama, NIP, atau jabatan</label>
                    <input type="text" id="searchInput" placeholder="Cari nama / NIP / jabatan...">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle employee-table mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4" style="width: 56px;">No</th>
                            <th>Nama &amp; NIP/NIPPPK</th>
                            <th>Jabatan</th>
                            <th class="text-center" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="employeeTable">
                        @php $no = 1; @endphp
                        @forelse($employees as $e)
                        <tr class="employee-row">
                            <td class="ps-4 text-muted font-monospace">{{ $no++ }}</td>
                            <td>
                                <div class="employee-name emp-name">{{ $e->nama }}</div>
                                <small class="employee-nip emp-nip font-monospace">{{ $e->nip }}</small>
                            </td>
                            <td>
                                <span class="role-pill emp-jabatan">{{ $e->jabatan }}</span>
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <button type="button" class="btn-icon" data-bs-toggle="modal" data-bs-target="#editModal{{ $e->id }}" aria-label="Edit data {{ $e->nama }}" title="Edit {{ $e->nama }}">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>

                                    <form action="{{ route('employee.destroy', $e->id) }}" method="POST" class="delete-form" data-name="{{ $e->nama }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon is-danger" aria-label="Hapus data {{ $e->nama }}" title="Hapus {{ $e->nama }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <div class="text-muted mb-2">Belum ada pegawai terdaftar.</div>
                                <a href="#nip" class="btn btn-outline-secondary btn-sm">Tambah Pegawai</a>
                            </td>
                        </tr>
                        @endforelse

                        <tr id="noResultRow" style="display: none;">
                            <td colspan="4" class="text-center py-4 text-muted">
                                <i class="bi bi-search me-2"></i> Pegawai tidak ditemukan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    @foreach($employees as $e)
        <div class="modal fade" id="editModal{{ $e->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $e->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('employee.update', $e->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel{{ $e->id }}">Edit Data Pegawai</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body text-start">
                            <div class="mb-3">
                                <label for="edit-nip-{{ $e->id }}" class="form-label">NIP/NIPPPK</label>
                                <input type="text" id="edit-nip-{{ $e->id }}" name="nip" class="form-control input-nip-edit" value="{{ $e->nip }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit-nama-{{ $e->id }}" class="form-label">Nama Lengkap</label>
                                <input type="text" id="edit-nama-{{ $e->id }}" name="nama" class="form-control" value="{{ $e->nama }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit-jabatan-{{ $e->id }}" class="form-label">Jabatan</label>
                                <input type="text" id="edit-jabatan-{{ $e->id }}" name="jabatan" class="form-control" value="{{ $e->jabatan }}" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
@endsection

@push('scripts')
    <script>
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('.employee-row');
            let hasVisibleRow = false;

            rows.forEach(function(row) {
                let name = row.querySelector('.emp-name').innerText.toLowerCase();
                let nip = row.querySelector('.emp-nip').innerText.toLowerCase();
                let jabatan = row.querySelector('.emp-jabatan').innerText.toLowerCase();

                if (name.includes(filter) || nip.includes(filter) || jabatan.includes(filter)) {
                    row.style.display = '';
                    hasVisibleRow = true;
                } else {
                    row.style.display = 'none';
                }
            });

            let noResultRow = document.getElementById('noResultRow');
            noResultRow.style.display = (hasVisibleRow || rows.length === 0) ? 'none' : '';
        });

        document.addEventListener('input', function (e) {
            if (e.target.classList.contains('input-nip') || e.target.classList.contains('input-nip-edit')) {
                let x = e.target.value.replace(/\D/g, '').match(/(\d{0,8})(\d{0,6})(\d{0,1})(\d{0,3})/);
                e.target.value = !x[2] ? x[1] : x[1] + ' ' + x[2] + (x[3] ? ' ' + x[3] : '') + (x[4] ? ' ' + x[4] : '');
            }
        });

        document.querySelectorAll('.delete-form').forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!confirm('Yakin hapus ' + form.dataset.name + '?')) {
                    event.preventDefault();
                }
            });
        });
    </script>
@endpush
