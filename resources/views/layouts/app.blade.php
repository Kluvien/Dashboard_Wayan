<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - SIKM Wayan</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --blue: #477EF7;
            --blue-soft: #EAF1FF;
            --page-bg: #F4F6FB;
            --text-dark: #20242A;
            --text-muted: #8A8D91;
            --border: #E2E5EA;
            --green-soft: #C9F3EE;
            --green-text: #00A990;
            --pink-soft: #FFD0E8;
            --pink-text: #FF2F8A;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--page-bg);
            color: var(--text-dark);
            font-family: Arial, Helvetica, sans-serif;
        }

        .app-wrapper {
            display: flex;
            min-height: 100vh;
            min-width: 1100px;
            background: var(--page-bg);
        }

        .sidebar {
            width: 270px;
            min-height: 100vh;
            background: #ffffff;
            border-right: 1px solid var(--border);
            flex-shrink: 0;
        }

        .brand {
            height: 70px;
            display: flex;
            align-items: center;
            padding: 0 30px;
            font-size: 20px;
            font-weight: 800;
        }

        .brand .brand-blue {
            color: var(--blue);
            margin-right: 4px;
        }

        .sidebar-menu {
            padding-top: 8px;
        }

        .sidebar-link,
        .sidebar-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 52px;
            padding: 0 30px;
            color: #171A1F;
            text-decoration: none;
            font-size: 16px;
            font-weight: 500;
        }

        .sidebar-link:hover {
            background: #F3F6FF;
            color: var(--blue);
        }

        .sidebar-link.active {
            background: var(--blue);
            color: #ffffff;
        }

        .sidebar-sub {
            padding-left: 20px;
        }

        .sidebar-sub .sidebar-link {
            padding-left: 30px;
            font-size: 15px;
        }

        .main-area {
            flex: 1;
            min-width: 0;
        }

        .topbar {
            height: 70px;
            background: #ffffff;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px 0 50px;
        }

        .search-box {
            width: 295px;
            height: 48px;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: #F6F7FB;
            display: flex;
            align-items: center;
            padding: 0 18px;
            color: var(--text-muted);
        }

        .search-box input {
            border: none;
            outline: none;
            background: transparent;
            width: 100%;
            margin-left: 10px;
            color: var(--text-dark);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .role-pill {
            border: 1px solid #BFC3CA;
            border-radius: 10px;
            padding: 8px 14px;
            background: #ffffff;
            font-size: 14px;
            font-weight: 600;
        }

        .user-box {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, #FFB4D8, #8B5CF6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-weight: 800;
        }

        .user-name {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .user-role {
            font-size: 12px;
            color: #666;
        }

        .content-wrapper {
            padding: 32px 28px;
        }

        .page-heading {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 26px;
        }

        .page-heading .muted {
            color: #8C8C8C;
        }

        .card {
            border: 1px solid var(--border);
            border-radius: 14px;
            background: #ffffff;
            box-shadow: none;
            padding: 22px;
        }

        table {
            width: 100%;
            background: #ffffff;
            border: 1px solid var(--border) !important;
            border-radius: 12px;
            overflow: hidden;
        }

        table th {
            background: #ffffff;
            color: #34383F;
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            padding: 18px 22px !important;
            border-bottom: 1px solid var(--border) !important;
        }

        table td {
            padding: 18px 22px !important;
            border-bottom: 1px solid var(--border) !important;
            vertical-align: middle;
        }

        .table-blue th {
            background: var(--blue) !important;
            color: #ffffff !important;
            text-transform: none;
            font-size: 18px;
        }

        .btn {
            border-radius: 8px;
            font-weight: 700;
            padding: 8px 18px;
        }

        .btn-primary {
            background: var(--blue);
            border-color: var(--blue);
        }

        .btn-edit {
            background: var(--green-soft);
            color: var(--green-text);
            border: none;
            min-width: 92px;
        }

        .btn-delete {
            background: var(--pink-soft);
            color: var(--pink-text);
            border: none;
            min-width: 92px;
        }

        .status-success {
            color: #39B52A;
            font-weight: 800;
        }

        .status-danger {
            color: #FF2F2F;
            font-weight: 800;
        }

        .form-control,
        .form-select {
            border-radius: 0;
            min-height: 46px;
            border: 1px solid #BFC3CA;
        }

        .dropdown-menu {
            border-radius: 12px;
            border: 1px solid var(--border);
        }
    </style>
</head>
<body>
@auth
    @php
        $role = auth()->user()->role;

        $profileUrl = '#';

        if ($role === 'Ketua Lab') {
            $profileUrl = '/ketualab/profil';
        } elseif ($role === 'Anggota') {
            $profileUrl = '/anggota/profil';
        } elseif ($role === 'Ketua KK') {
            $profileUrl = '/ketuakk/dashboard';
        }
    @endphp

    <div class="app-wrapper">
        <aside class="sidebar">
            <div class="brand">
                <span class="brand-blue">KM</span> EIMS
            </div>

            <nav class="sidebar-menu">
                @if($role === 'Ketua KK')
                    <a class="sidebar-link {{ request()->is('ketuakk/dashboard') ? 'active' : '' }}" href="/ketuakk/dashboard">
                        <span>Dashboard</span>
                    </a>

                    <div class="sidebar-heading">
                        <span>Data Master</span>
                        <i class="bi bi-chevron-down"></i>
                    </div>

                    <div class="sidebar-sub">
                        <a class="sidebar-link {{ request()->is('ketuakk/data-lab-riset') ? 'active' : '' }}" href="/ketuakk/data-lab-riset">
                            <span>Data Lab. Riset</span>
                        </a>

                        <a class="sidebar-link {{ request()->is('ketuakk/data-dosen') ? 'active' : '' }}" href="/ketuakk/data-dosen">
                            <span>Data Anggota KK</span>
                        </a>
                    </div>

                    <div class="sidebar-heading">
                        <span>Kontrak Manajemen</span>
                        <i class="bi bi-chevron-down"></i>
                    </div>

                    <div class="sidebar-sub">
                        <a class="sidebar-link {{ request()->is('ketuakk/km-kk*') ? 'active' : '' }}" href="/ketuakk/km-kk">
                            <span>Kelompok Keahlian</span>
                        </a>

                        <a class="sidebar-link {{ request()->is('ketuakk/km-lab-riset*') ? 'active' : '' }}" href="/ketuakk/km-lab-riset">
                            <span>Lab. Riset</span>
                        </a>

                        <a class="sidebar-link {{ request()->is('ketuakk/km-anggota-kk*') ? 'active' : '' }}" href="/ketuakk/km-anggota-kk">
                            <span>Anggota KK</span>
                        </a>
                    </div>

                    <div class="sidebar-heading">
                        <span>Monitoring</span>
                        <i class="bi bi-chevron-down"></i>
                    </div>

                    <div class="sidebar-sub">
                        <a class="sidebar-link {{ request()->is('ketuakk/monitoring-lab-riset*') ? 'active' : '' }}" href="/ketuakk/monitoring-lab-riset">
                            <span>Monitoring Lab. Riset</span>
                        </a>

                        <a class="sidebar-link {{ request()->is('ketuakk/monitoring-anggota-kk*') ? 'active' : '' }}" href="/ketuakk/monitoring-anggota-kk">
                            <span>Monitoring Anggota KK</span>
                        </a>
                    </div>
                @elseif($role === 'Ketua Lab')
                    <a class="sidebar-link {{ request()->is('ketualab/dashboard') ? 'active' : '' }}" href="/ketualab/dashboard">
                        <span>Dashboard</span>
                    </a>

                    <a class="sidebar-link {{ request()->is('ketualab/penurunan-km*') ? 'active' : '' }}" href="/ketualab/penurunan-km">
                        <span>Target KM Anggota</span>
                    </a>

                    <a class="sidebar-link {{ request()->is('ketualab/monitoring-lab') ? 'active' : '' }}" href="/ketualab/monitoring-lab">
                        <span>Monitoring KM Lab</span>
                    </a>

                    <a class="sidebar-link {{ request()->is('ketualab/monitoring-anggota') || request()->is('ketualab/detail-anggota*') ? 'active' : '' }}" href="/ketualab/monitoring-anggota">
                        <span>Monitoring Anggota</span>
                    </a>

                    <a class="sidebar-link {{ request()->is('ketualab/laporan') ? 'active' : '' }}" href="/ketualab/laporan">
                        <span>Laporan</span>
                    </a>

                    <a class="sidebar-link {{ request()->is('ketualab/profil') ? 'active' : '' }}" href="/ketualab/profil">
                        <span>Profil</span>
                    </a>
                @elseif($role === 'Anggota')
                    <a class="sidebar-link {{ request()->is('anggota/dashboard') ? 'active' : '' }}" href="/anggota/dashboard">
                        <span>Dashboard</span>
                    </a>

                    <a class="sidebar-link {{ request()->is('anggota/profil') ? 'active' : '' }}" href="/anggota/profil">
                        <span>Profil</span>
                    </a>

                    <a class="sidebar-link {{ request()->is('anggota/aktivitas-km*') ? 'active' : '' }}" href="/anggota/aktivitas-km">
                        <span>Aktivitas KM</span>
                    </a>

                    <a class="sidebar-link {{ request()->is('anggota/riwayat-realisasi') ? 'active' : '' }}" href="/anggota/riwayat-realisasi">
                        <span>Riwayat Realisasi</span>
                    </a>

                    <a class="sidebar-link {{ request()->is('anggota/progress-km') ? 'active' : '' }}" href="/anggota/progress-km">
                        <span>Progress KM</span>
                    </a>
                @endif
            </nav>
        </aside>

        <main class="main-area">
            <header class="topbar">
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" placeholder="Cari">
                </div>

                <div class="topbar-right">
                    <div class="role-pill">{{ auth()->user()->role }} EIMS</div>

                    <div class="dropdown">
                        <button class="btn p-0 border-0 bg-transparent dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <div class="user-box">
                                <div class="avatar">
                                    {{ strtoupper(substr(auth()->user()->username, 0, 1)) }}
                                </div>

                                <div class="text-start">
                                    <div class="user-name">{{ auth()->user()->username }}</div>
                                    <div class="user-role">{{ auth()->user()->role }}</div>
                                </div>
                            </div>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li>
                                <a class="dropdown-item" href="{{ $profileUrl }}">
                                    Profil
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form action="/logout" method="POST">
                                    @csrf
                                    <button class="dropdown-item text-danger" type="submit">
                                        Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <section class="content-wrapper">
                @yield('content')
            </section>
        </main>
    </div>
@else
    @yield('content')
@endauth

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

@if(session('success') || session('error'))
<div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 18px;">
            <div class="modal-body text-center p-5">
                @if(session('success'))
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center"
                         style="width: 72px; height: 72px; border-radius: 50%; background:#E9F8EF; color:#28A745;">
                        <i class="bi bi-check-lg" style="font-size: 38px;"></i>
                    </div>

                    <h4 class="fw-bold mb-2">Data Berhasil Diproses</h4>
                    <p class="text-muted mb-4">{{ session('success') }}</p>

                    <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">
                        Oke
                    </button>
                @endif

                @if(session('error'))
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center"
                         style="width: 72px; height: 72px; border-radius: 50%; background:#FDECEC; color:#DC3545;">
                        <i class="bi bi-x-lg" style="font-size: 32px;"></i>
                    </div>

                    <h4 class="fw-bold mb-2">Data Gagal Diproses</h4>
                    <p class="text-muted mb-4">{{ session('error') }}</p>

                    <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">
                        Oke
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 18px;">
            <div class="modal-body text-center p-5">
                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center"
                     style="width: 72px; height: 72px; border-radius: 50%; background:#FDECEC; color:#DC3545;">
                    <i class="bi bi-trash3" style="font-size: 34px;"></i>
                </div>

                <h4 class="fw-bold mb-2">Konfirmasi Hapus Data</h4>
                <p class="text-muted mb-4" id="deleteConfirmText">
                    Apakah Anda yakin ingin menghapus data ini?
                </p>

                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button type="button" class="btn btn-delete px-4" id="deleteConfirmButton">
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const statusModalElement = document.getElementById('statusModal');

        if (statusModalElement && window.bootstrap) {
            const statusModal = new bootstrap.Modal(statusModalElement);
            statusModal.show();
        }

        const deleteModalElement = document.getElementById('deleteConfirmModal');
        const deleteButton = document.getElementById('deleteConfirmButton');
        const deleteText = document.getElementById('deleteConfirmText');

        let selectedDeleteForm = null;

        document.querySelectorAll('.js-delete-form').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();

                selectedDeleteForm = form;

                const message = form.getAttribute('data-message') || 'Apakah Anda yakin ingin menghapus data ini?';
                deleteText.textContent = message;

                if (window.bootstrap) {
                    const deleteModal = new bootstrap.Modal(deleteModalElement);
                    deleteModal.show();
                }
            });
        });

        if (deleteButton) {
            deleteButton.addEventListener('click', function () {
                if (selectedDeleteForm) {
                    selectedDeleteForm.submit();
                }
            });
        }
    });
</script>

</body>
</html>