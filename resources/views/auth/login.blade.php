@extends('layouts.app')

@section('title', 'Masuk')
@section('hideNav', '1')
@section('errorTitle', 'Gagal masuk.')

@push('styles')
    <style>
        .login-wrap {
            width: 100%;
            max-width: 430px;
            margin: clamp(24px, 7vh, 72px) auto 0;
        }

        .login-panel { overflow: hidden; }

        .login-head {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 20px;
            background: #f1ede3;
            border-bottom: 1px solid var(--line);
        }

        .login-logo {
            width: 44px;
            height: 44px;
            object-fit: contain;
        }

        .login-head h1 {
            margin: 0;
            color: var(--navy);
            font-family: var(--font-display);
            font-size: 1.35rem;
            font-weight: 600;
            line-height: 1.2;
        }

        .login-head p {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: .82rem;
        }

        .login-panel .panel-body { padding: 22px; }

        .login-help {
            margin: 16px 0 0;
            color: var(--muted);
            font-size: .78rem;
            line-height: 1.5;
            text-align: center;
        }

        .login-note {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 14px;
            color: var(--muted);
            font-size: .76rem;
            text-align: center;
        }

        @media (max-width: 640px) {
            .login-wrap { margin-top: 18px; }
        }
    </style>
@endpush

@section('content')
    <div class="login-wrap">
        <section class="panel login-panel" aria-labelledby="login-title">
            <div class="login-head">
                <img src="{{ asset('img/pupr.png') }}" alt="" class="login-logo">
                <div>
                    <h1 id="login-title">Masuk ke Rekap Absen</h1>
                    <p>Gunakan akun yang diberikan administrator.</p>
                </div>
            </div>

            <div class="panel-body">
                <form method="POST" action="{{ route('login.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus autocomplete="username">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Kata Sandi</label>
                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check mb-4">
                        <input type="checkbox" id="remember" name="remember" class="form-check-input" value="1" {{ old('remember') ? 'checked' : '' }}>
                        <label for="remember" class="form-check-label">Ingat saya</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                    </button>
                </form>

                <p class="login-help">Belum punya akses? Hubungi administrator untuk pembuatan akun.</p>
            </div>
        </section>

        <p class="login-note">
            <i class="bi bi-shield-lock" aria-hidden="true"></i>
            Akses terbatas untuk pegawai Dinas PUPR Kabupaten Kutai Barat.
        </p>
    </div>
@endsection
