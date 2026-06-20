@extends('layouts.app')

@section('title', 'Monitoring KM Lab')

@section('content')
<div class="card">
    <h2>Monitoring KM Laboratorium</h2>
    <p>
        Halaman ini digunakan Ketua Lab untuk melihat capaian Kontrak Manajemen
        laboratorium secara keseluruhan.
    </p>

    @php
        $laboratories = [
            [
                'kode' => 'BMS',
                'nama_lab' => 'Business Modelling & Simulation',
                'target' => 10,
                'realisasi' => 7,
                'persentase' => 70,
                'status' => 'Berjalan',
            ],
            [
                'kode' => 'PMDT',
                'nama_lab' => 'Project Management & Digital Talent',
                'target' => 8,
                'realisasi' => 8,
                'persentase' => 100,
                'status' => 'Tercapai',
            ],
            [
                'kode' => 'ESS',
                'nama_lab' => 'Enterprise System and Solution',
                'target' => 12,
                'realisasi' => 6,
                'persentase' => 50,
                'status' => 'Berjalan',
            ],
            [
                'kode' => 'ReaLISM',
                'nama_lab' => 'E-Logistic and Supply Chain',
                'target' => 9,
                'realisasi' => 5,
                'persentase' => 56,
                'status' => 'Berjalan',
            ],
            [
                'kode' => 'DMI',
                'nama_lab' => 'Digital Marketing and Intelligence',
                'target' => 7,
                'realisasi' => 7,
                'persentase' => 100,
                'status' => 'Tercapai',
            ],
        ];
    @endphp

    <table border="1" cellpadding="10" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Lab</th>
                <th>Nama Laboratorium</th>
                <th>Target</th>
                <th>Realisasi</th>
                <th>Persentase</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($laboratories as $index => $lab)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $lab['kode'] }}</td>
                    <td>{{ $lab['nama_lab'] }}</td>
                    <td>{{ $lab['target'] }}</td>
                    <td>{{ $lab['realisasi'] }}</td>
                    <td>{{ $lab['persentase'] }}%</td>
                    <td>{{ $lab['status'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection