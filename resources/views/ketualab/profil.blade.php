@extends('layouts.app')

@section('title', 'Profil Ketua Lab')

@section('content')
<div class="card">
    <h2>Profil Ketua Lab</h2>
    <p>Halaman ini digunakan Ketua Lab untuk melihat data profil.</p>

    <table border="1" cellpadding="10" cellspacing="0" width="100%">
        <tr>
            <th>Username</th>
            <td>{{ auth()->user()->username }}</td>
        </tr>
        <tr>
            <th>Role</th>
            <td>{{ auth()->user()->role }}</td>
        </tr>
    </table>
</div>
@endsection