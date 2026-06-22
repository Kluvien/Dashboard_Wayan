@extends('layouts.app')

@section('title', 'Monitoring Anggota')

@section('content')
<div class="card">
    <h2>Monitoring KM Anggota</h2>
    <p>
        Halaman ini digunakan Ketua Lab untuk memantau capaian Kontrak Manajemen
        anggota yang berada di laboratorium risetnya.
    </p>

    <table border="1" cellpadding="10" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Anggota</th>
                <th>NIDN</th>
                <th>Email</th>
                <th>Lab Riset</th>
                <th>Total Target</th>
                <th>Total Realisasi</th>
                <th>Progress</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataMonitoring as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item['nama_dosen'] }}</td>
                    <td>{{ $item['nidn'] }}</td>
                    <td>{{ $item['email'] }}</td>
                    <td>{{ $item['nama_lab'] }}</td>
                    <td>{{ $item['total_target'] }}</td>
                    <td>{{ $item['total_realisasi'] }}</td>
                    <td>
                        <div style="background: #e5e7eb; width: 100%; height: 20px;">
                            <div style="background: #22c55e; width: {{ $item['persentase'] }}%; height: 20px;"></div>
                        </div>
                        {{ $item['persentase'] }}%
                    </td>
                    <td>{{ $item['status'] }}</td>
                    <td>
                        <a href="/ketualab/detail-anggota/{{ $item['id_user'] }}">Detail</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10">Belum ada anggota pada laboratorium ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection