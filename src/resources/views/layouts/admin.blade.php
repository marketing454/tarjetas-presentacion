<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — Team Presentation</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --sidebar-bg: #111111;
            --sidebar-hover: #1c1c1c;
            --sidebar-active: #8dc63f;
            --sidebar-width: 260px;
            --topbar-height: 60px;
            --accent: #8dc63f;
            /* Override Bootstrap blue globally */
            --bs-primary: #8dc63f;
            --bs-primary-rgb: 141, 198, 63;
            --bs-link-color: #8dc63f;
            --bs-link-hover-color: #76b52e;
            --bs-link-color-rgb: 141, 198, 63;
        }

        body { background: #f1f5f9; font-family: 'Segoe UI', system-ui, sans-serif; }

        /* Sidebar */
        #sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            color: #cbd5e1;
            z-index: 1050;
            display: flex;
            flex-direction: column;
            transition: transform .3s ease;
        }
        .sidebar-brand {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #222;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: .3rem;
        }
        .sidebar-brand img {
            height: 38px;
            width: auto;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }
        .sidebar-brand small {
            display: block;
            font-size: .7rem;
            color: #64748b;
            font-weight: 400;
            padding-left: 2px;
        }

        .sidebar-nav { flex: 1; padding: 1rem 0; overflow-y: auto; }
        .sidebar-section {
            padding: .3rem 1.5rem .15rem;
            font-size: .65rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #475569;
            margin-top: .5rem;
        }
        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .65rem 1.5rem;
            color: #94a3b8;
            border-radius: 0;
            font-size: .875rem;
            transition: all .2s;
            border-left: 3px solid transparent;
        }
        .sidebar-nav .nav-link i { width: 18px; text-align: center; font-size: .9rem; }
        .sidebar-nav .nav-link:hover {
            background: var(--sidebar-hover);
            color: #e2e8f0;
            border-left-color: #334155;
        }
        .sidebar-nav .nav-link.active {
            background: rgba(141, 198, 63, .12);
            color: #fff;
            border-left-color: var(--accent);
            font-weight: 600;
        }
        .sidebar-nav .nav-link .badge { margin-left: auto; }

        .sidebar-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #222;
            font-size: .8rem;
            color: #475569;
        }

        /* Topbar */
        #topbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--topbar-height);
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            gap: 1rem;
            z-index: 1040;
        }
        #topbar .page-title {
            font-size: 1rem;
            font-weight: 600;
            color: #0f172a;
            flex: 1;
        }
        #topbar .user-menu .btn {
            border: none;
            background: #f8fafc;
            color: #475569;
            font-size: .85rem;
            font-weight: 500;
        }
        .toggle-btn {
            background: none;
            border: none;
            color: #64748b;
            font-size: 1.2rem;
            cursor: pointer;
            display: none;
        }

        /* Content */
        #main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--topbar-height);
            padding: 2rem;
            min-height: calc(100vh - var(--topbar-height));
        }

        /* Cards */
        .stat-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,.06);
        }
        .stat-card .icon-box {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem;
        }

        .content-card {
            background: #fff;
            border-radius: 12px;
            border: none;
            box-shadow: 0 1px 3px rgba(0,0,0,.06);
        }
        .content-card .card-header {
            background: none;
            border-bottom: 1px solid #f1f5f9;
            padding: 1.25rem 1.5rem;
            font-weight: 600;
            color: #0f172a;
        }

        /* Table */
        .table th {
            font-size: .75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #64748b;
            border-top: none;
            background: #f8fafc;
        }
        .table td { vertical-align: middle; font-size: .875rem; }
        .table tbody tr:hover { background: #f8fafc; }

        /* Avatar */
        .avatar {
            width: 38px; height: 38px;
            border-radius: 50%;
            object-fit: cover;
            background: #e2e8f0;
            display: flex; align-items: center; justify-content: center;
            font-weight: 600; font-size: .85rem;
            color: #475569;
            flex-shrink: 0;
        }
        .avatar-lg { width: 64px; height: 64px; font-size: 1.2rem; }

        /* Badges */
        .badge-branch {
            background: #f0f9e0;
            color: #5a8a1a;
            font-weight: 500;
            font-size: .7rem;
        }

        /* Alerts */
        .alert { border: none; border-radius: 10px; }

        /* Buttons */
        .btn-primary { background: var(--accent); border-color: var(--accent); color: #111; }
        .btn-primary:hover { background: #76b52e; border-color: #76b52e; color: #111; }

        /* QR Modal */
        #qrPreview { max-width: 220px; }

        /* Sobreescribir azules Bootstrap restantes */
        .form-check-input:checked {
            background-color: #8dc63f;
            border-color: #8dc63f;
        }
        .form-check-input:focus {
            border-color: #8dc63f;
            box-shadow: 0 0 0 .25rem rgba(141,198,63,.25);
        }
        .form-control:focus, .form-select:focus {
            border-color: #8dc63f;
            box-shadow: 0 0 0 .25rem rgba(141,198,63,.2);
        }
        .spinner-border.text-primary { color: #8dc63f !important; }
        .text-primary { color: #8dc63f !important; }
        a { color: #5a8a1a; }
        a:hover { color: #76b52e; }
        .btn-outline-primary { color: #8dc63f; border-color: #8dc63f; }
        .btn-outline-primary:hover { background: #8dc63f; border-color: #8dc63f; color: #111; }
        .page-link { color: #8dc63f; }
        .page-item.active .page-link { background: #8dc63f; border-color: #8dc63f; color: #111; }
        .nav-tabs .nav-link.active { color: #8dc63f; border-bottom-color: #8dc63f; }
        .progress-bar { background-color: #8dc63f; }

        /* Responsive */
        @media (max-width: 768px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.show { transform: translateX(0); }
            #topbar { left: 0; }
            #main-content { margin-left: 0; }
            .toggle-btn { display: block; }
        }
    </style>
    @stack('styles')
</head>
<body>

{{-- Sidebar --}}
<nav id="sidebar">
    <div class="sidebar-brand">
        <img src="{{ asset('Logo-compulago-corporativo.png') }}" alt="COMPULAGO">
        <small>Team Presentation</small>
    </div>

    <div class="sidebar-nav">
        <div class="sidebar-section">General</div>
        <a href="{{ route('admin.dashboard') }}"
           class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="fas fa-gauge-high"></i> Dashboard
        </a>

        <div class="sidebar-section">Gestión</div>
        <a href="{{ route('admin.branches.index') }}"
           class="nav-link {{ request()->routeIs('admin.branches.*') ? 'active' : '' }}">
            <i class="fas fa-building"></i> Sucursales
        </a>
        <a href="{{ route('admin.employees.index') }}"
           class="nav-link {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}">
            <i class="fas fa-users"></i> Empleados
        </a>
        <a href="{{ route('admin.card-banners.index') }}"
           class="nav-link {{ request()->routeIs('admin.card-banners.*') ? 'active' : '' }}">
            <i class="fas fa-image"></i> Banners
        </a>
    </div>

    <div class="sidebar-footer">
        <div class="d-flex align-items-center gap-2">
            <div class="avatar" style="width:32px;height:32px;font-size:.75rem;background:#1e293b;color:#94a3b8;">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>
            <div style="line-height:1.2">
                <div style="color:#e2e8f0;font-size:.8rem;font-weight:600;">{{ auth()->user()->name }}</div>
                <div style="font-size:.7rem;">{{ auth()->user()->email }}</div>
            </div>
        </div>
        <form action="{{ route('auth.logout') }}" method="POST" class="mt-2">
            @csrf
            <button type="submit" class="btn btn-sm w-100" style="background:#1e293b;color:#94a3b8;border:none;font-size:.75rem;">
                <i class="fas fa-right-from-bracket me-1"></i> Cerrar sesión
            </button>
        </form>
    </div>
</nav>

{{-- Topbar --}}
<div id="topbar">
    <button class="toggle-btn" onclick="document.getElementById('sidebar').classList.toggle('show')">
        <i class="fas fa-bars"></i>
    </button>
    <div class="page-title">@yield('page-title', 'Dashboard')</div>
    <div class="d-flex align-items-center gap-2">
        @yield('topbar-actions')
    </div>
</div>

{{-- Content --}}
<main id="main-content">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="fas fa-circle-check"></i>
            {{ session('success') }}
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="fas fa-circle-exclamation"></i>
            {{ session('error') }}
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @yield('content')
</main>

{{-- QR Modal global --}}
<div class="modal fade" id="qrModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold">Código QR</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div id="qrContainer" class="d-flex justify-content-center align-items-center" style="min-height:220px;">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
                <p class="text-muted small mt-2 mb-0" id="qrEmployeeName"></p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
                <a id="qrDownloadBtn" href="#" class="btn btn-primary btn-sm px-4">
                    <i class="fas fa-download me-1"></i> Descargar PNG
                </a>
                <a id="qrCardBtn" href="#" target="_blank" class="btn btn-outline-secondary btn-sm px-4">
                    <i class="fas fa-arrow-up-right-from-square me-1"></i> Ver tarjeta
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showQr(employeeId, employeeName, downloadUrl, cardUrl) {
    document.getElementById('qrEmployeeName').textContent = employeeName;
    document.getElementById('qrDownloadBtn').href = downloadUrl;
    document.getElementById('qrCardBtn').href = cardUrl;
    document.getElementById('qrContainer').innerHTML = '<div class="spinner-border text-primary" role="status"></div>';

    const modal = new bootstrap.Modal(document.getElementById('qrModal'));
    modal.show();

    fetch(`/admin/employees/${employeeId}/qr-preview`)
        .then(r => r.text())
        .then(svg => {
            document.getElementById('qrContainer').innerHTML = svg;
            document.getElementById('qrContainer').querySelector('svg').setAttribute('id', 'qrPreview');
        });
}

// Cerrar sidebar al hacer click fuera (móvil)
document.addEventListener('click', function(e) {
    const sidebar = document.getElementById('sidebar');
    if (window.innerWidth <= 768 && sidebar.classList.contains('show')
        && !sidebar.contains(e.target) && !e.target.closest('.toggle-btn')) {
        sidebar.classList.remove('show');
    }
});
</script>
@stack('scripts')
</body>
</html>
