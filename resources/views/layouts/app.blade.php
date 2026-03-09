<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'App Vuelos' }} – GIRAG</title>
    <link rel="icon" type="image/png" href="/images/logo.png">

    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        /* ═══════════════════════════════════════════════
           DESIGN SYSTEM — GIRAG App Vuelos
           ═══════════════════════════════════════════════ */
        :root {
            --sidebar-bg:      #0f172a;
            --sidebar-width:   260px;
            --sidebar-hover:   rgba(255,255,255,0.07);
            --sidebar-active:  rgba(99,102,241,0.18);
            --sidebar-active-border: #6366f1;
            --accent:          #6366f1;
            --accent-hover:    #4f46e5;
            --page-bg:         #f1f5f9;
            --card-bg:         #ffffff;
            --card-radius:     14px;
            --card-shadow:     0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.06);
            --card-shadow-hover: 0 4px 12px rgba(0,0,0,.10), 0 12px 32px rgba(0,0,0,.08);
            --text-primary:    #0f172a;
            --text-secondary:  #64748b;
            --text-muted:      #94a3b8;
            --border:          #e2e8f0;
            --input-bg:        #f8fafc;
            --transition:      all .18s ease;
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--page-bg);
            color: var(--text-primary);
            margin: 0;
            font-size: 14px;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* ─── SCROLLBAR ─────────────────────────────── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }

        /* ═══════════════════════════════════════════════
           SIDEBAR
           ═══════════════════════════════════════════════ */
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            display: flex;
            flex-direction: column;
            z-index: 1000;
            overflow: hidden;
        }

        /* Brand */
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 24px 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            text-decoration: none;
        }
        .sidebar-brand-logo {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            object-fit: contain;
            background: rgba(255,255,255,0.1);
            padding: 4px;
        }
        .sidebar-brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }
        .sidebar-brand-text span:first-child {
            font-size: 13px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: -.01em;
        }
        .sidebar-brand-text span:last-child {
            font-size: 10px;
            font-weight: 500;
            color: rgba(255,255,255,0.4);
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        /* Nav */
        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 16px 12px;
        }
        .sidebar-nav::-webkit-scrollbar { width: 0; }

        .sidebar-section-label {
            font-size: 10px;
            font-weight: 600;
            color: rgba(255,255,255,0.28);
            text-transform: uppercase;
            letter-spacing: .1em;
            padding: 12px 8px 6px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: 9px;
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            transition: var(--transition);
            margin-bottom: 2px;
            position: relative;
        }
        .sidebar-link:hover {
            background: var(--sidebar-hover);
            color: rgba(255,255,255,0.9);
        }
        .sidebar-link.active {
            background: var(--sidebar-active);
            color: #ffffff;
            font-weight: 600;
        }
        .sidebar-link.active::before {
            content: '';
            position: absolute;
            left: 0; top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 60%;
            background: var(--sidebar-active-border);
            border-radius: 0 3px 3px 0;
        }
        .sidebar-link .nav-icon {
            width: 18px;
            text-align: center;
            font-size: 13px;
            opacity: .85;
            flex-shrink: 0;
        }

        /* User section */
        .sidebar-user {
            padding: 16px 16px 20px;
            border-top: 1px solid rgba(255,255,255,0.06);
        }
        .sidebar-user-inner {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar-avatar {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            color: white;
            flex-shrink: 0;
        }
        .sidebar-user-info {
            flex: 1;
            min-width: 0;
        }
        .sidebar-user-name {
            font-size: 13px;
            font-weight: 600;
            color: rgba(255,255,255,0.9);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sidebar-user-role {
            font-size: 10px;
            color: rgba(255,255,255,0.35);
            text-transform: uppercase;
            letter-spacing: .06em;
        }
        .sidebar-logout {
            width: 28px;
            height: 28px;
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255,255,255,0.35);
            transition: var(--transition);
            cursor: pointer;
            border: none;
            background: transparent;
            flex-shrink: 0;
        }
        .sidebar-logout:hover {
            background: rgba(239,68,68,0.15);
            color: #f87171;
        }

        /* ═══════════════════════════════════════════════
           CONTENT
           ═══════════════════════════════════════════════ */
        .page-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            padding: 32px;
        }

        /* Page header */
        .page-header {
            margin-bottom: 28px;
        }
        .page-title {
            font-size: 22px;
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: -.03em;
            line-height: 1.2;
            margin: 0;
        }
        .page-subtitle {
            font-size: 13px;
            color: var(--text-secondary);
            margin-top: 4px;
        }

        /* ═══════════════════════════════════════════════
           CARDS
           ═══════════════════════════════════════════════ */
        .app-card {
            background: var(--card-bg);
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border);
            overflow: hidden;
        }
        .app-card-header {
            padding: 18px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .app-card-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }
        .app-card-body {
            padding: 24px;
        }

        /* Metric cards */
        .metric-card {
            background: var(--card-bg);
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border);
            padding: 22px 24px;
            position: relative;
            overflow: hidden;
            transition: var(--transition);
        }
        .metric-card:hover {
            box-shadow: var(--card-shadow-hover);
            transform: translateY(-1px);
        }
        .metric-card-label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }
        .metric-card-value {
            font-size: 36px;
            font-weight: 800;
            letter-spacing: -.04em;
            line-height: 1;
            color: var(--text-primary);
        }
        .metric-card-icon {
            position: absolute;
            top: 20px; right: 20px;
            width: 40px; height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }
        .metric-card-accent {
            position: absolute;
            bottom: 0; left: 0;
            height: 3px;
            width: 100%;
        }

        /* ═══════════════════════════════════════════════
           TABLES
           ═══════════════════════════════════════════════ */
        .app-table {
            width: 100%;
            border-collapse: collapse;
        }
        .app-table thead th {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--text-secondary);
            background: #f8fafc;
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }
        .app-table tbody td {
            padding: 13px 16px;
            border-bottom: 1px solid #f1f5f9;
            color: var(--text-primary);
            font-size: 13.5px;
            vertical-align: middle;
        }
        .app-table tbody tr:last-child td { border-bottom: none; }
        .app-table tbody tr {
            transition: background .12s ease;
        }
        .app-table tbody tr:hover td { background: #f8fafc; }

        /* ── Livewire table sortable headers ─────────────────────── */
        .sort-btn {
            background: none;
            border: none;
            padding: 0;
            font: inherit;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--text-secondary);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: color .15s ease;
        }
        .sort-btn:hover { color: var(--accent); }
        .sort-btn .sort-icon { font-size: 9px; opacity: .5; }
        .sort-btn.active { color: var(--accent); }
        .sort-btn.active .sort-icon { opacity: 1; }

        /* Livewire pagination */
        nav[aria-label="Pagination Navigation"] { padding: 12px 20px; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid var(--border); flex-wrap: wrap; gap: 8px; }
        nav[aria-label="Pagination Navigation"] p { font-size: 12px; color: var(--text-muted); margin: 0; }
        nav[aria-label="Pagination Navigation"] span[aria-disabled="true"] > span,
        nav[aria-label="Pagination Navigation"] button {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 30px; height: 30px; padding: 0 8px;
            border-radius: 7px; font-size: 12px; font-family: 'Inter', sans-serif;
            border: 1px solid var(--border); background: white; color: var(--text-secondary);
            cursor: pointer; transition: var(--transition); text-decoration: none;
        }
        nav[aria-label="Pagination Navigation"] button:hover { background: #f1f5f9; color: var(--text-primary); }
        nav[aria-label="Pagination Navigation"] button[aria-current="page"],
        nav[aria-label="Pagination Navigation"] span[aria-current="page"] > span {
            background: var(--accent); border-color: var(--accent); color: white;
        }
        nav[aria-label="Pagination Navigation"] span[aria-disabled="true"] > span { opacity: .4; cursor: not-allowed; }

        /* Search input in tables */
        .table-search {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
        }
        .table-search-input {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 7px 12px 7px 34px;
            font-size: 13px;
            font-family: 'Inter', sans-serif;
            color: var(--text-primary);
            background: var(--input-bg) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.3-4.3'/%3E%3C/svg%3E") no-repeat 10px center;
            outline: none;
            min-width: 220px;
            transition: border-color .15s;
        }
        .table-search-input:focus { border-color: var(--accent); background-color: white; }
        .table-meta { font-size: 12px; color: var(--text-muted); margin-left: auto; }

        /* ═══════════════════════════════════════════════
           BADGES / STATUS PILLS
           ═══════════════════════════════════════════════ */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .02em;
        }
        .status-pill::before {
            content: '';
            width: 6px; height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .status-pending  { background: #fef3c7; color: #92400e; }
        .status-pending::before  { background: #f59e0b; }
        .status-approved { background: #dbeafe; color: #1d4ed8; }
        .status-approved::before { background: #3b82f6; }
        .status-billed   { background: #d1fae5; color: #065f46; }
        .status-billed::before   { background: #10b981; }

        /* ═══════════════════════════════════════════════
           ACTION BUTTONS
           ═══════════════════════════════════════════════ */
        .action-btn {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: var(--text-secondary);
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
        }
        .action-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 8px rgba(0,0,0,.1); }
        .action-btn-view:hover   { background: #eff6ff; border-color: #bfdbfe; color: #2563eb; }
        .action-btn-edit:hover   { background: #f0fdf4; border-color: #bbf7d0; color: #16a34a; }
        .action-btn-approve:hover { background: #eef2ff; border-color: #c7d2fe; color: #4f46e5; }
        .action-btn-bill:hover   { background: #ecfdf5; border-color: #a7f3d0; color: #059669; }
        .action-btn-revert:hover { background: #fffbeb; border-color: #fde68a; color: #d97706; }
        .action-btn-delete:hover { background: #fff1f2; border-color: #fecdd3; color: #e11d48; }
        .action-btn.disabled     { opacity: .35; pointer-events: none; }
        .action-btn-group        { display: flex; gap: 4px; }

        /* ═══════════════════════════════════════════════
           FORM INPUTS
           ═══════════════════════════════════════════════ */
        .form-control, .form-select {
            font-family: 'Inter', sans-serif !important;
            font-size: 13.5px;
            border: 1px solid var(--border);
            border-radius: 9px;
            background: var(--input-bg);
            color: var(--text-primary);
            padding: 9px 14px;
            transition: var(--transition);
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(99,102,241,.12);
            background: white;
            outline: none;
        }
        .form-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: 6px;
        }
        .form-control.is-invalid, .form-select.is-invalid { border-color: #f43f5e; }
        .invalid-feedback { font-size: 12px; }

        /* ═══════════════════════════════════════════════
           BUTTONS
           ═══════════════════════════════════════════════ */
        .btn {
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            font-size: 13px;
            border-radius: 9px;
            padding: 9px 18px;
            transition: var(--transition);
            letter-spacing: -.01em;
        }
        .btn:hover { transform: translateY(-1px); }
        .btn:active { transform: translateY(0); }
        .btn-primary {
            background: var(--accent);
            border-color: var(--accent);
        }
        .btn-primary:hover {
            background: var(--accent-hover);
            border-color: var(--accent-hover);
            box-shadow: 0 4px 12px rgba(99,102,241,.35);
        }
        .btn-secondary {
            background: white;
            border-color: var(--border);
            color: var(--text-secondary);
        }
        .btn-secondary:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: var(--text-primary);
        }
        .btn-sm { padding: 6px 12px; font-size: 12px; border-radius: 7px; }
        .btn-danger {
            background: #f43f5e;
            border-color: #f43f5e;
        }
        .btn-danger:hover {
            background: #e11d48;
            border-color: #e11d48;
            box-shadow: 0 4px 12px rgba(244,63,94,.3);
        }
        .btn-success {
            background: #10b981;
            border-color: #10b981;
        }
        .btn-success:hover {
            background: #059669;
            border-color: #059669;
            box-shadow: 0 4px 12px rgba(16,185,129,.3);
        }

        /* ═══════════════════════════════════════════════
           FLASH MESSAGES
           ═══════════════════════════════════════════════ */
        #flash-zone {
            position: fixed;
            top: 20px;
            left: calc(var(--sidebar-width) + 50%);
            transform: translateX(-50%);
            z-index: 9999;
            min-width: 320px;
        }
        .flash-alert {
            background: white;
            border: 1px solid var(--border);
            border-radius: 11px;
            box-shadow: 0 8px 24px rgba(0,0,0,.12);
            padding: 13px 18px;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown .25s ease forwards;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .flash-alert-success { border-left: 4px solid #10b981; }
        .flash-alert-success .flash-icon { color: #10b981; }
        .flash-alert-error { border-left: 4px solid #f43f5e; }
        .flash-alert-error .flash-icon { color: #f43f5e; }

        /* ═══════════════════════════════════════════════
           UTILITIES
           ═══════════════════════════════════════════════ */
        .text-xs { font-size: 11px; }
        .text-sm { font-size: 13px; }
        .fw-semibold { font-weight: 600; }
        .fw-heavy { font-weight: 800; }
        .text-muted-custom { color: var(--text-muted); }
        .divider { border: none; border-top: 1px solid var(--border); }
        .rounded-app { border-radius: var(--card-radius); }

        /* ═══════════════════════════════════════════════
           BOOTSTRAP OVERRIDES
           ═══════════════════════════════════════════════ */
        .card { border-radius: var(--card-radius); border: 1px solid var(--border); box-shadow: var(--card-shadow); }
        .card-header { background: #f8fafc; border-bottom: 1px solid var(--border); font-size: 13px; font-weight: 600; }
        .badge { font-family: 'Inter', sans-serif; font-weight: 600; }
        .alert { border-radius: 10px; font-size: 13px; }
        .list-group-item { font-size: 13px; }
        .modal-content { border-radius: 16px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,.15); }
        .modal-header { border-bottom: 1px solid var(--border); }
        .modal-footer { border-top: 1px solid var(--border); }
    </style>

    @livewireStyles
</head>
<body>

    <!-- ═══════════════════════════════════════════════
         SIDEBAR
         ═══════════════════════════════════════════════ -->
    <nav class="sidebar">
        <!-- Brand -->
        <a href="{{ route('dashboard') }}" class="sidebar-brand">
            <img src="/images/logo.png" alt="GIRAG" class="sidebar-brand-logo">
            <div class="sidebar-brand-text">
                <span>App Vuelos</span>
                <span>GIRAG</span>
            </div>
        </a>

        <!-- Nav -->
        <div class="sidebar-nav">
            <div class="sidebar-section-label">Principal</div>
            <a href="{{ route('dashboard') }}"
               class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge-high nav-icon"></i>
                Dashboard
            </a>
            <a href="{{ route('flights.index') }}"
               class="sidebar-link {{ request()->routeIs('flights.*') ? 'active' : '' }}">
                <i class="fa-solid fa-plane nav-icon"></i>
                Vuelos
            </a>

            @can('manage-catalogs')
            <div class="sidebar-section-label" style="margin-top:8px;">Catálogos</div>
            <a href="{{ route('airlines.index') }}"
               class="sidebar-link {{ request()->routeIs('airlines.*') ? 'active' : '' }}">
                <i class="fa-solid fa-building-columns nav-icon"></i>
                Aerolíneas
            </a>
            <a href="{{ route('aircraft.index') }}"
               class="sidebar-link {{ request()->routeIs('aircraft.*') ? 'active' : '' }}">
                <i class="fa-solid fa-jet-fighter nav-icon"></i>
                Aeronaves
            </a>
            <a href="{{ route('document-types.index') }}"
               class="sidebar-link {{ request()->routeIs('document-types.*') ? 'active' : '' }}">
                <i class="fa-solid fa-folder-open nav-icon"></i>
                Tipos de Documento
            </a>
            <a href="{{ route('users.index') }}"
               class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="fa-solid fa-users nav-icon"></i>
                Usuarios
            </a>
            @endcan

            @can('export-sage')
            <div class="sidebar-section-label" style="margin-top:8px;">Herramientas</div>
            <a href="{{ route('odoo.import') }}"
               class="sidebar-link {{ request()->routeIs('odoo.*') ? 'active' : '' }}">
                <i class="fa-solid fa-file-arrow-up nav-icon"></i>
                Importar Odoo
            </a>
            @endcan

        </div>

        <!-- User -->
        <div class="sidebar-user">
            <div class="sidebar-user-inner">
                <div class="sidebar-avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
                    <div class="sidebar-user-role">
                        @if(auth()->user()->is_admin_vuelos) Admin
                        @elseif(auth()->user()->is_billing_supervisor) Facturación
                        @elseif(auth()->user()->is_flight_supervisor) Supervisor
                        @else Operador
                        @endif
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="sidebar-logout" title="Cerrar sesión">
                        <i class="fa-solid fa-right-from-bracket" style="font-size:13px;"></i>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- ═══════════════════════════════════════════════
         CONTENT
         ═══════════════════════════════════════════════ -->
    <main class="page-content">
        <!-- Flash -->
        <div id="flash-zone">
            @if(session('message'))
            <div class="flash-alert flash-alert-success">
                <i class="fa-solid fa-circle-check flash-icon"></i>
                <span>{{ session('message') }}</span>
            </div>
            @endif
            @if(session('error'))
            <div class="flash-alert flash-alert-error">
                <i class="fa-solid fa-circle-exclamation flash-icon"></i>
                <span>{{ session('error') }}</span>
            </div>
            @endif
        </div>

        {{ $slot }}
    </main>

    <!-- ═══════════════════════════════════════════════
         SCRIPTS
         ═══════════════════════════════════════════════ -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.to/flatpickr/dist/l10n/es.js"></script>

    <script>
        // Auto-dismiss flash after 4s
        document.addEventListener('DOMContentLoaded', () => {
            const flash = document.querySelectorAll('#flash-zone .flash-alert');
            if (flash.length) {
                setTimeout(() => flash.forEach(el => {
                    el.style.opacity = '0';
                    el.style.transition = 'opacity .3s ease';
                    setTimeout(() => el.remove(), 300);
                }), 4000);
            }
        });

        // Livewire: open/close Bootstrap modals via events
        document.addEventListener('livewire:init', () => {
            Livewire.on('open-modal', (id) => {
                const el = document.getElementById(Array.isArray(id) ? id[0] : id);
                if (el) bootstrap.Modal.getOrCreateInstance(el).show();
            });
            Livewire.on('close-modal', (id) => {
                const el = document.getElementById(Array.isArray(id) ? id[0] : id);
                if (el) bootstrap.Modal.getOrCreateInstance(el).hide();
            });
        });
    </script>

    @livewireScripts
    @stack('scripts')
</body>
</html>
