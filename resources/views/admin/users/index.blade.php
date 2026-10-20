@extends('layouts.app')

@section('title', 'Pengguna & Role')

@section('heading', 'Pengguna & Role')
@section('subheading', 'Kelola akun admin dan operator yang dapat mengakses aplikasi.')

@section('actions')
    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke Dashboard
    </a>
@endsection

@push('styles')
    <style>
        .admin-layout {
            display: grid;
            grid-template-columns: minmax(280px, 340px) minmax(0, 1fr);
            gap: 18px;
            align-items: start;
        }

        .admin-layout > * { min-width: 0; }

        .form-stack {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .form-stack .form-text {
            color: var(--muted);
            font-size: .78rem;
        }

        .user-table { min-width: 660px; }

        .user-name {
            color: var(--ink);
            font-weight: 600;
        }

        .user-email {
            margin-top: 2px;
            color: var(--muted);
            font-size: .8rem;
            overflow-wrap: anywhere;
        }

        .you-marker {
            display: inline-block;
            margin-left: 6px;
            padding: 1px 6px;
            border: 1px solid var(--line-strong);
            border-radius: 999px;
            color: var(--muted);
            font-size: .68rem;
            font-weight: 600;
            vertical-align: 1px;
        }

        .badge-role,
        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .02em;
            white-space: nowrap;
        }

        .badge-role-admin {
            color: var(--navy);
            background: var(--gold-soft);
            border: 1px solid #dcc7a6;
        }

        .badge-role-operator {
            color: #4b5a66;
            background: #f1ede3;
            border: 1px solid var(--line);
        }

        .badge-status.is-active {
            color: var(--success);
            background: #eef5f0;
            border: 1px solid #c6dbce;
        }

        .badge-status.is-inactive {
            color: var(--danger);
            background: #fbeeee;
            border: 1px solid #e5c4c4;
        }

        .empty-users {
            padding: 28px 18px;
            color: var(--muted);
            text-align: center;
        }

        @media (max-width: 992px) {
            .admin-layout { grid-template-columns: minmax(0, 1fr); }
        }
    </style>
@endpush

@section('content')
    <div class="admin-layout">
        <section class="panel" aria-labelledby="create-user-title">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title" id="create-user-title">Tambah Pengguna</h2>
                    <p class="panel-subtitle">Buat akun baru untuk admin atau operator.</p>
                </div>
            </div>
            <div class="panel-body">
                <form action="{{ route('admin.users.store') }}" method="POST" class="form-stack">
                    @csrf

                    <div>
                        <label for="name" class="form-label">Nama lengkap</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
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
                            value="{{ old('email') }}"
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
                        <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                            @foreach(\App\Models\User::roles() as $role)
                                <option value="{{ $role }}" @selected(old('role', \App\Models\User::ROLE_OPERATOR) === $role)>
                                    {{ ucfirst($role) }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Admin dapat mengelola pengguna; operator mengelola absensi dan laporan.</div>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="form-label">Kata sandi</label>
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
                        <label for="password_confirmation" class="form-label">Ulangi kata sandi</label>
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

                    <div class="form-check">
                        <input type="hidden" name="is_active" value="0">
                        <input
                            type="checkbox"
                            id="is_active"
                            name="is_active"
                            value="1"
                            class="form-check-input"
                            @checked(old('is_active', true))
                        >
                        <label for="is_active" class="form-check-label">Akun aktif</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-person-plus" aria-hidden="true"></i> Buat Pengguna
                    </button>
                </form>
            </div>
        </section>

        <section class="panel" aria-labelledby="user-list-title">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title" id="user-list-title">Daftar Pengguna</h2>
                    <p class="panel-subtitle">
                        {{ $users->count() }} akun &middot; {{ $users->where('is_active', true)->count() }} aktif
                    </p>
                </div>
            </div>

            @if($users->isEmpty())
                <div class="empty-users">
                    <i class="bi bi-people" aria-hidden="true"></i>
                    <p class="mb-0 mt-2">Belum ada pengguna.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle user-table">
                        <thead>
                            <tr>
                                <th scope="col">Nama</th>
                                <th scope="col">Role</th>
                                <th scope="col">Status</th>
                                <th scope="col">Dibuat</th>
                                <th scope="col" class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td>
                                        <div class="user-name">
                                            {{ $user->name }}
                                            @if($user->is(auth()->user()))
                                                <span class="you-marker">Anda</span>
                                            @endif
                                        </div>
                                        <div class="user-email">{{ $user->email }}</div>
                                    </td>
                                    <td>
                                        <span class="badge-role badge-role-{{ $user->role }}">{{ $user->roleLabel() }}</span>
                                    </td>
                                    <td>
                                        <span class="badge-status {{ $user->is_active ? 'is-active' : 'is-inactive' }}">
                                            <i class="bi {{ $user->is_active ? 'bi-check-circle' : 'bi-slash-circle' }}" aria-hidden="true"></i>
                                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td>{{ $user->created_at?->format('d/m/Y') }}</td>
                                    <td class="text-end">
                                        <a
                                            href="{{ route('admin.users.edit', $user) }}"
                                            class="btn btn-sm btn-outline-secondary"
                                            aria-label="Edit pengguna {{ $user->name }}"
                                        >
                                            <i class="bi bi-pencil-square" aria-hidden="true"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
@endsection
