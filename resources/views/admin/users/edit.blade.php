@extends('layouts.app')

@section('title', 'Edit Pengguna')

@section('heading', 'Edit Pengguna')
@section('subheading', 'Perbarui profil, role, dan akses untuk '.$user->name.'.')

@section('actions')
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali
    </a>
@endsection

@push('styles')
    <style>
        .admin-edit-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.3fr) minmax(280px, .7fr);
            gap: 18px;
            align-items: start;
        }

        .admin-edit-grid > * { min-width: 0; }

        .form-stack {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .form-stack .form-text {
            color: var(--muted);
            font-size: .78rem;
        }

        .self-note {
            padding: 10px 12px;
            border: 1px solid #dcc7a6;
            border-radius: var(--radius);
            color: #6b542d;
            background: var(--gold-soft);
            font-size: .82rem;
        }

        .password-note {
            padding: 10px 12px;
            border-left: 3px solid var(--gold);
            color: var(--muted);
            background: #faf7ef;
            font-size: .82rem;
        }

        @media (max-width: 900px) {
            .admin-edit-grid { grid-template-columns: minmax(0, 1fr); }
        }
    </style>
@endpush

@section('content')
    @php($isSelf = $user->is(auth()->user()))

    <div class="admin-edit-grid">
        <section class="panel" aria-labelledby="profile-title">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title" id="profile-title">Profil &amp; Akses</h2>
                    <p class="panel-subtitle">{{ $user->email }}</p>
                </div>
            </div>
            <div class="panel-body">
                @if($isSelf)
                    <div class="self-note mb-3">
                        <i class="bi bi-shield-lock" aria-hidden="true"></i>
                        Role dan status akun sendiri tidak dapat diubah dari panel ini.
                    </div>
                @endif

                <form action="{{ route('admin.users.update', $user) }}" method="POST" class="form-stack">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="name" class="form-label">Nama lengkap</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            class="form-control @error('name') is-invalid @enderror"
                            autocomplete="name"
                            maxlength="255"
                            required
                        >
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="form-label">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email', $user->email) }}"
                            class="form-control @error('email') is-invalid @enderror"
                            autocomplete="email"
                            maxlength="255"
                            required
                        >
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="role" class="form-label">Role</label>
                        @if($isSelf)
                            <select id="role" class="form-select" aria-describedby="role-help" disabled>
                                <option selected>{{ $user->roleLabel() }}</option>
                            </select>
                            <input type="hidden" name="role" value="{{ $user->role }}">
                        @else
                            <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" aria-describedby="role-help" required>
                                @foreach(\App\Models\User::roles() as $role)
                                    <option value="{{ $role }}" @selected(old('role', $user->role) === $role)>
                                        {{ ucfirst($role) }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                        <div class="form-text" id="role-help">Admin dapat mengelola pengguna; operator mengelola absensi dan laporan.</div>
                        @error('role')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check">
                        @if($isSelf)
                            <input type="hidden" name="is_active" value="1">
                            <input type="checkbox" id="is_active" class="form-check-input" checked disabled>
                        @else
                            <input type="hidden" name="is_active" value="0">
                            <input
                                type="checkbox"
                                id="is_active"
                                name="is_active"
                                value="1"
                                class="form-check-input"
                                @checked(old('is_active', $user->is_active))
                            >
                        @endif
                        <label for="is_active" class="form-check-label">Akun aktif</label>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2" aria-hidden="true"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </section>

        <section class="panel" aria-labelledby="password-title">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title" id="password-title">Reset Kata Sandi</h2>
                    <p class="panel-subtitle">Kata sandi lama tidak dapat dilihat.</p>
                </div>
            </div>
            <div class="panel-body">
                <div class="password-note mb-3">
                    <i class="bi bi-key" aria-hidden="true"></i>
                    @if($isSelf)
                        Setelah disimpan, gunakan kata sandi baru pada login berikutnya.
                    @else
                        Sampaikan kata sandi baru kepada pengguna melalui kanal aman.
                    @endif
                </div>

                <form action="{{ route('admin.users.password', $user) }}" method="POST" class="form-stack">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="password" class="form-label">Kata sandi baru</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            autocomplete="new-password"
                            minlength="12"
                            aria-describedby="password-help"
                            required
                        >
                        <div class="form-text" id="password-help">Minimal 12 karakter.</div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="form-label">Ulangi kata sandi baru</label>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            class="form-control"
                            autocomplete="new-password"
                            minlength="12"
                            required
                        >
                    </div>

                    <button type="submit" class="btn btn-outline-secondary">
                        <i class="bi bi-key" aria-hidden="true"></i> Simpan Kata Sandi Baru
                    </button>
                </form>
            </div>
        </section>
    </div>
@endsection
