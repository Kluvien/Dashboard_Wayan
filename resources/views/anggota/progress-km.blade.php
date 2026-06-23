@extends('layouts.app')

@section('title', 'Progress KM Saya')

@section('content')
<div class="card">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="page-heading mb-0">
            Progress <span class="muted">KM Saya</span>
        </div>

        <a href="/anggota/dashboard" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
    
    <h2>Progress KM Saya</h2>
    <p>
        Halaman ini menampilkan progress capaian Kontrak Manajemen berdasarkan aktivitas KM yang telah diinput.
    </p>

    <div style="display: flex; gap: 16px; margin-bottom: 20px;">
        <div style="border: 1px solid #ddd; padding: 16px; width: 33%;">
            <h3>Total Target</h3>
            <p style="font-size: 24px;">{{ $totalTarget }}</p>
        </div>

        <div style="border: 1px solid #ddd; padding: 16px; width: 33%;">
            <h3>Total Realisasi</h3>
            <p style="font-size: 24px;">{{ $totalRealisasi }}</p>
        </div>

        <div style="border: 1px solid #ddd; padding: 16px; width: 33%;">
            <h3>Persentase Capaian</h3>
            <p style="font-size: 24px;">{{ min($persentaseTotal, 100) }}%</p>
        </div>
    </div>

    <h3>Progress Per Kategori</h3>

    <table border="1" cellpadding="10" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>No</th>
                <th>Kategori KM</th>
                <th>Target</th>
                <th>Realisasi</th>
                <th>Progress</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($progress as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item['kategori'] }}</td>
                    <td>{{ $item['target'] }}</td>
                    <td>{{ $item['realisasi'] }}</td>
                    <td>
                        <div style="background: #e5e7eb; width: 100%; height: 20px;">
                            <div style="background: #22c55e; width: {{ $item['persentase'] }}%; height: 20px;"></div>
                        </div>
                        {{ $item['persentase'] }}%
                    </td>
                    <td>{{ $item['status'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <br>

    <h3>Status Capaian</h3>
    <ul>
        @foreach($progress as $item)
            @if($item['status'] == 'Tercapai')
                <li>✔ {{ $item['kategori'] }} telah mencapai target.</li>
            @else
                <li>⚠ {{ $item['kategori'] }} belum mencapai target.</li>
            @endif
        @endforeach
    </ul>
</div>
@endsection