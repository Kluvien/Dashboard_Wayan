<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Dashboard KM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .sidebar { min-height: 100vh; background-color: #2c3e50; }
        .sidebar a { color: #ecf0f1; text-decoration: none; padding: 12px 20px; display: block; transition: 0.3s; }
        .sidebar a:hover { background-color: #34495e; border-left: 4px solid #3498db; }
        .content-area { background-color: #f8f9fa; }
    </style>
</head>
<body>
    <div class="d-flex">
        <div class="sidebar text-white shadow" style="width: 250px;">
            <div class="p-3 fs-5 fw-bold border-bottom border-secondary text-center">
                <i class="bi bi-bar-chart-fill me-2"></i>SIKM Wayan
            </div>
            <div class="mt-3">
                <p class="px-3 text-secondary text-uppercase fw-bold" style="font-size: 0.75rem;">Menu Utama</p>
                
                @if(auth()->user()->role == 'Ketua KK')
                    <h4>Ketua KK</h4>
                    <a href="/ketuakk/dashboard">Dashboard</a>
                    <a href="/ketuakk/target-km">Kelola Target KM</a>

                    <h4>Data Master</h4>
                    <a href="/ketuakk/data-dosen">Data Dosen</a>
                    <a href="/ketuakk/data-lab-riset">Data Lab Riset</a>
                    <a href="/ketuakk/data-kelompok-keahlian">Data Kelompok Keahlian</a>
                
                
                @elseif(auth()->user()->role == 'Ketua Lab')
                    <a href="/ketualab/dashboard"></i> Dashboard</a>
                    <a href="/ketualab/penurunan-km"></i> Target KM Anggota</a>
                    <a href="/ketualab/monitoring-lab"></i> Monitoring KM Lab</a>
                    <a href="/ketualab/monitoring-anggota"></i> Monitoring Anggota</a>
                    <a href="/ketualab/laporan"></i> Laporan</a>
                    <a href="/ketualab/profil"></i> Profil</a>
                

                @elseif(auth()->user()->role == 'Anggota')
                    <a href="/anggota/dashboard"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                    <a href="/anggota/realisasi-km"><i class="bi bi-pencil-square me-2"></i> Input Realisasi</a>
                    <a href="/anggota/aktivitas-km">Aktivitas KM</a>
                    <a href="/anggota/riwayat-realisasi">Riwayat Realisasi</a>
                    <a href="/anggota/profil">Profil</a>
                @endif
            </div>
        </div>

        <div class="flex-grow-1 content-area">
            <div class="bg-white p-3 d-flex justify-content-between align-items-center shadow-sm">
                <h4 class="mb-0 fw-bold text-secondary">@yield('title')</h4>
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle border" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i> 
                        {{ auth()->user()->username }} ({{ auth()->user()->role }})
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Profil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="/logout" method="POST">
                                @csrf
                                <button class="dropdown-item text-danger" type="submit">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="p-4">
                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>