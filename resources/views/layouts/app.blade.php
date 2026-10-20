<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Rekap Absen') &middot; Dinas PUPR Kutai Barat</title>
    <link rel="icon" href="{{ asset('img/pupr.png') }}" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --ink: #202a33;
            --muted: #61707c;
            --paper: #f4f1e9;
            --surface: #fffdf8;
            --line: #d9d2c3;
            --line-strong: #b8ae9c;
            --navy: #17324d;
            --navy-strong: #0f2438;
            --gold: #a67c3d;
            --gold-soft: #f1e6d4;
            --danger: #8f2d2d;
            --success: #2f634a;
            --radius: 3px;
            --shadow: 0 1px 0 rgba(23, 50, 77, .03), 0 10px 28px rgba(23, 50, 77, .05);
            --font-ui: "Segoe UI", system-ui, -apple-system, "Helvetica Neue", Arial, sans-serif;
            --font-display: Georgia, "Times New Roman", serif;
        }

        body {
            min-height: 100vh;
            background: var(--paper);
            color: var(--ink);
            font-family: var(--font-ui);
            display: flex;
            flex-direction: column;
        }

        a { color: var(--navy); }

        .skip-link {
            position: absolute;
            top: -48px;
            left: 16px;
            z-index: 1100;
            padding: 8px 12px;
            background: var(--surface);
            color: var(--navy);
            border: 1px solid var(--navy);
            border-radius: var(--radius);
            font-weight: 600;
        }

        .skip-link:focus { top: 12px; }

        .site-header {
            background: var(--navy);
            border-bottom: 3px solid var(--gold);
            color: var(--surface);
        }

        .header-inner {
            max-width: 1220px;
            margin: 0 auto;
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            color: inherit;
            text-decoration: none;
        }

        .brand:hover { color: inherit; }

        .brand-logo {
            height: 38px;
            width: auto;
            object-fit: contain;
        }

        .brand-copy {
            display: flex;
            flex-direction: column;
            min-width: 0;
            padding-left: 4px;
        }

        .brand-copy strong {
            font-family: var(--font-display);
            font-size: 1.18rem;
            font-weight: 600;
            letter-spacing: .01em;
            line-height: 1.2;
            white-space: nowrap;
        }

        .brand-copy small {
            color: rgba(255, 253, 248, .68);
            font-size: .68rem;
            letter-spacing: .075em;
            line-height: 1.35;
            text-transform: uppercase;
        }

        .site-nav {
            display: flex;
            align-items: center;
            gap: 2px;
            flex-wrap: wrap;
        }

        .site-nav a {
            color: rgba(255, 253, 248, .76);
            text-decoration: none;
            font-size: .86rem;
            font-weight: 600;
            padding: 8px 10px;
            border-bottom: 2px solid transparent;
            white-space: nowrap;
        }

        .site-nav a:hover,
        .site-nav a:focus-visible {
            color: var(--surface);
            border-bottom-color: rgba(255, 253, 248, .4);
        }

        .site-nav a.is-active {
            color: var(--surface);
            border-bottom-color: var(--gold);
        }

        .nav-form {
            display: flex;
            margin: 0;
        }

        .nav-form button {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 8px 10px;
            color: rgba(255, 253, 248, .76);
            background: transparent;
            border: 0;
            border-bottom: 2px solid transparent;
            font-family: inherit;
            font-size: .86rem;
            font-weight: 600;
            white-space: nowrap;
            cursor: pointer;
        }

        .nav-form button:hover,
        .nav-form button:focus-visible {
            color: var(--surface);
            border-bottom-color: rgba(255, 253, 248, .4);
        }

        .page-shell {
            flex: 1;
            width: 100%;
            max-width: 1220px;
            margin: 0 auto;
            padding: 30px 24px 52px;
        }

        .page-head {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--line-strong);
        }

        .page-head h1 {
            margin: 0;
            font-family: var(--font-display);
            font-size: clamp(1.65rem, 2.4vw, 2.15rem);
            font-weight: 600;
            line-height: 1.15;
            color: var(--navy);
        }

        .page-head p {
            margin: 5px 0 0;
            color: var(--muted);
            font-size: .92rem;
        }

        .page-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 8px;
        }

        .btn {
            border-radius: var(--radius);
            font-size: .88rem;
            font-weight: 600;
            padding: .52rem .9rem;
        }

        .btn-primary {
            background-color: var(--navy);
            border-color: var(--navy);
            color: var(--surface);
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background-color: var(--navy-strong);
            border-color: var(--navy-strong);
            color: var(--surface);
        }

        .btn-outline-secondary {
            color: var(--navy);
            border-color: var(--line-strong);
            background: transparent;
        }

        .btn-outline-secondary:hover,
        .btn-outline-secondary:focus {
            color: var(--navy-strong);
            background: var(--gold-soft);
            border-color: var(--gold);
        }

        .btn-outline-danger {
            color: var(--danger);
            border-color: #d8b6b6;
            background: transparent;
        }

        .btn-outline-danger:hover,
        .btn-outline-danger:focus {
            color: #fff;
            background: var(--danger);
            border-color: var(--danger);
        }

        .btn-outline-success {
            color: var(--success);
            border-color: #b6d0c2;
            background: transparent;
        }

        .btn-outline-success:hover,
        .btn-outline-success:focus {
            color: #fff;
            background: var(--success);
            border-color: var(--success);
        }

        .form-control,
        .form-select {
            border-color: var(--line-strong);
            border-radius: var(--radius);
            color: var(--ink);
            background-color: #fffefb;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--navy);
            box-shadow: 0 0 0 .18rem rgba(23, 50, 77, .12);
        }

        .form-label {
            margin-bottom: .35rem;
            color: #4b5a66;
            font-size: .84rem;
            font-weight: 600;
        }

        .panel {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 18px;
            border-bottom: 1px solid var(--line);
        }

        .panel-body { padding: 18px; }

        .panel-title {
            margin: 0;
            color: var(--navy);
            font-family: var(--font-display);
            font-size: 1.08rem;
            font-weight: 600;
        }

        .panel-subtitle {
            margin: 2px 0 0;
            color: var(--muted);
            font-size: .8rem;
        }

        .table {
            --bs-table-bg: transparent;
            --bs-table-color: var(--ink);
            --bs-table-border-color: var(--line);
            margin-bottom: 0;
            font-size: .9rem;
        }

        .table > :not(caption) > * > * {
            padding: .68rem .6rem;
            border-bottom-color: var(--line);
        }

        .table thead th {
            background: #f1ede3;
            color: var(--muted);
            font-size: .69rem;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
            vertical-align: middle;
            border-bottom: 1px solid var(--line-strong);
        }

        .table-hover > tbody > tr:hover > * {
            --bs-table-accent-bg: #faf7ef;
            background-color: #faf7ef;
        }

        .table-responsive { border-radius: 0 0 var(--radius) var(--radius); }

        .alert {
            border-radius: var(--radius);
            border: 1px solid;
            font-size: .9rem;
        }

        .alert-success {
            color: #244f3b;
            background: #eef5f0;
            border-color: #c6dbce;
        }

        .alert-danger {
            color: #742424;
            background: #fbeeee;
            border-color: #e5c4c4;
        }

        .site-footer {
            width: 100%;
            max-width: 1220px;
            margin: 0 auto;
            padding: 18px 24px 24px;
            border-top: 1px solid var(--line);
            color: var(--muted);
            font-size: .76rem;
            display: flex;
            justify-content: space-between;
            gap: 8px;
            flex-wrap: wrap;
        }

        :focus-visible {
            outline: 2px solid var(--gold);
            outline-offset: 2px;
        }

        .visually-hidden-focusable:focus {
            position: static !important;
            width: auto !important;
            height: auto !important;
            padding: .5rem .75rem !important;
            overflow: visible !important;
            clip: auto !important;
            white-space: normal !important;
        }

        @media (max-width: 900px) {
            .header-inner {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
                padding: 13px 18px;
            }

            .brand-copy small { display: none; }
            .brand-logo { height: 33px; }

            .site-nav {
                width: 100%;
                flex-wrap: nowrap;
                overflow-x: auto;
                padding-bottom: 2px;
            }

            .site-nav a,
            .nav-form button { padding: 8px; }

            .site-nav.has-admin {
                flex-wrap: wrap;
                overflow-x: visible;
            }

            .page-head {
                flex-direction: column;
                align-items: flex-start;
            }

            .page-actions {
                width: 100%;
                justify-content: flex-start;
            }
        }

        @media (max-width: 640px) {
            .page-shell { padding: 20px 14px 40px; }
            .site-footer { padding: 16px 14px 22px; }
            .page-head { margin-bottom: 18px; }
            .panel-body { padding: 14px; }
            .panel-header { padding: 12px 14px; }
        }
    </style>
    @stack('styles')
</head>
<body>
<a class="skip-link" href="#main-content">Lewati ke konten utama</a>

<header class="site-header">
    <div class="header-inner">
        <a class="brand" href="{{ route('dashboard') }}" aria-label="Rekap Absen, kembali ke dashboard">
            <img src="{{ asset('img/kubar.png') }}" alt="Logo Kabupaten Kutai Barat" class="brand-logo">
            <img src="{{ asset('img/pupr.png') }}" alt="Logo Dinas Pekerjaan Umum dan Penataan Ruang" class="brand-logo">
            <span class="brand-copy">
                <strong>Rekap Absen</strong>
                <small>Dinas Pekerjaan Umum &amp; Penataan Ruang &middot; Kutai Barat</small>
            </span>
        </a>

        @unless ($__env->hasSection('hideNav'))
            <nav class="site-nav {{ auth()->check() && auth()->user()->isAdmin() ? 'has-admin' : '' }}" aria-label="Navigasi utama">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'is-active' : '' }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif>Dashboard</a>
                <a href="{{ route('attendance.create') }}" class="{{ request()->routeIs('attendance.create') ? 'is-active' : '' }}" @if(request()->routeIs('attendance.create')) aria-current="page" @endif>Input Absensi</a>
                <a href="{{ route('attendance.rekap') }}" class="{{ request()->routeIs('attendance.rekap') ? 'is-active' : '' }}" @if(request()->routeIs('attendance.rekap')) aria-current="page" @endif>Laporan TPP</a>
                <a href="{{ route('employee.create') }}" class="{{ request()->routeIs('employee.create') ? 'is-active' : '' }}" @if(request()->routeIs('employee.create')) aria-current="page" @endif>Pegawai</a>

                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}" @if(request()->routeIs('admin.users.*')) aria-current="page" @endif>Pengguna</a>
                    @endif

                    <form action="{{ route('logout') }}" method="POST" class="nav-form">
                        @csrf
                        <button type="submit" aria-label="Keluar dari aplikasi">
                            <i class="bi bi-box-arrow-right" aria-hidden="true"></i> Keluar
                        </button>
                    </form>
                @endauth
            </nav>
        @endunless
    </div>
</header>

<main id="main-content" class="page-shell">
    @if(session('success'))
        <div class="alert alert-success d-flex align-items-start gap-2" role="status">
            <i class="bi bi-check-circle-fill mt-1"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> @yield('errorTitle', 'Data belum disimpan.')</div>
            <ul class="mb-0 ps-4">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @hasSection('heading')
        <div class="page-head">
            <div>
                <h1>@yield('heading')</h1>
                @hasSection('subheading')
                    <p>@yield('subheading')</p>
                @endif
            </div>
            @hasSection('actions')
                <div class="page-actions">@yield('actions')</div>
            @endif
        </div>
    @endif

    @yield('content')
</main>

<footer class="site-footer">
    <span>Rekap Absen &middot; Dinas Pekerjaan Umum &amp; Penataan Ruang Kabupaten Kutai Barat</span>
    <span>{{ now()->locale('id')->isoFormat('YYYY') }}</span>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
