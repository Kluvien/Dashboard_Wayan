@extends('layouts.app')

@section('title', 'Data Dosen')

@section('content')
@include('ketuakk.data-master._styles')
<style>
    .pagination-custom {
        gap: 0;
    }

    .pagination-custom .page-item .page-link {
        min-width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-color: #D9DEE7;
        color: #8A2E2A;
        background: #FFFFFF;
        font-size: 14px;
        font-weight: 600;
        box-shadow: none;
    }

    .pagination-custom .page-item:first-child .page-link {
        border-top-left-radius: 5px;
        border-bottom-left-radius: 5px;
    }

    .pagination-custom .page-item:last-child .page-link {
        border-top-right-radius: 5px;
        border-bottom-right-radius: 5px;
    }

    .pagination-custom .page-item.active .page-link {
        background: #A8322B;
        border-color: #A8322B;
        color: #FFFFFF;
    }

    .pagination-custom .page-item.disabled .page-link {
        color: #A0A8B5;
        background: #F8FAFC;
        border-color: #E3E8F0;
        cursor: not-allowed;
    }

    .pagination-custom .page-item:not(.active):not(.disabled) .page-link:hover {
        background: #FFF3F2;
        color: #8A2E2A;
    }

    .pagination-info {
        font-size: 13px;
        color: var(--text-muted);
    }

    @media (max-width: 768px) {
        .data-table-footer {
            align-items: flex-start !important;
        }

        .pagination-custom {
            flex-wrap: wrap;
        }
    }
</style>

<section class="ketuakk-master">
    <div class="ketuakk-master__header">
        <div>
            <div class="ketuakk-master__eyebrow">Data Master Ketua KK</div>
            <h1 class="ketuakk-master__title">Daftar Data Anggota KK</h1>
            <p class="ketuakk-master__description">
                Halaman ini digunakan untuk melihat dan mengelola data dosen anggota Kelompok Keahlian.
            </p>
        </div>

        <a href="/ketuakk/data-dosen/create" class="ketuakk-master__button ketuakk-master__button--primary">
            <i class="bi bi-plus-lg me-1"></i>
            Input Data Dosen
        </a>
    </div>

    <div class="ketuakk-master__toolbar">
    <form action="/ketuakk/data-dosen" method="GET" class="ketuakk-master__search">
                <label for="master-dosen-search" class="visually-hidden">Cari data dosen</label>
                <input
                    type="text"
                    id="master-dosen-search"
                    name="q"
                    value="{{ $q ?? '' }}"
                    class="form-control"
                    placeholder="Cari nama, NIDN, email, JAD, atau lab riset...">
                    <button type="submit" class="ketuakk-master__button ketuakk-master__button--primary">
                        Cari
                    </button>

                    <a href="/ketuakk/data-dosen" class="ketuakk-master__button">
                        Reset
                    </a>
    </form>
    </div>

    <div class="table-responsive ketuakk-master__scroll">
        <table
            class="table align-middle mb-0 ketuakk-master__table">

            <thead>
                <tr>
                    <th style="width: 9%;">No</th>
                    <th style="width: 20%;">Nama Dosen</th>
                    <th style="width: 11%;">NIDN</th>
                    <th style="width: 21%;">Email</th>
                    <th style="width: 9%;">JAD</th>
                    <th style="width: 22%;">Lab Riset</th>
                    <th style="width: 12%;">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($dosens as $index => $dosen)
                    <tr>
                        <td>
                            {{ $dosens->firstItem() + $index }}
                        </td>

                        <td class="ketuakk-master__cell--identity">
                            {{ $dosen->nama_dosen }}
                        </td>

                        <td>
                            {{ $dosen->nidn }}
                        </td>

                        <td style="word-break: break-word;">
                            {{ $dosen->email }}
                        </td>

                        <td>
                            <span class="ketuakk-master__label">
                                {{ $dosen->jad ?? '-' }}
                            </span>
                        </td>

                        <td>
                            {{ $dosen->nama_lab ?? '-' }}
                        </td>

                        <td>
                            <div class="ketuakk-master__actions-inline">
                                <a
                                    href="/ketuakk/data-dosen/{{ $dosen->id_dosen }}/edit"
                                    class="ketuakk-master__button">
                                    Ubah
                                </a>

                                <form
                                    action="/ketuakk/data-dosen/{{ $dosen->id_dosen }}"
                                    method="POST"
                                    class="js-delete-form"
                                    data-message="Apakah Anda yakin ingin menghapus data dosen ini?">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="ketuakk-master__button ketuakk-master__button--danger">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="ketuakk-master__empty">
                            Belum ada data dosen.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($dosens->total() > 0)
        <div class="data-table-footer ketuakk-master__footer">
            <div class="pagination-info">
                Menampilkan {{ $dosens->firstItem() }} - {{ $dosens->lastItem() }}
                dari {{ $dosens->total() }} data dosen
            </div>

            @if($dosens->hasPages())
                @php
                    $currentPage = $dosens->currentPage();
                    $lastPage = $dosens->lastPage();

                    $pages = [];

                    if ($lastPage <= 11) {
                        $pages = range(1, $lastPage);
                    } elseif ($currentPage <= 6) {
                        $pages = array_merge(
                            range(1, 10),
                            ['ellipsis-right'],
                            [$lastPage]
                        );
                    } elseif ($currentPage >= $lastPage - 5) {
                        $pages = array_merge(
                            [1],
                            ['ellipsis-left'],
                            range($lastPage - 9, $lastPage)
                        );
                    } else {
                        $pages = array_merge(
                            [1],
                            ['ellipsis-left'],
                            range($currentPage - 3, $currentPage + 3),
                            ['ellipsis-right'],
                            [$lastPage]
                        );
                    }
                @endphp

                <nav aria-label="Pagination Data Dosen">
                    <ul class="pagination pagination-custom mb-0">
                        <li class="page-item {{ $dosens->onFirstPage() ? 'disabled' : '' }}">
                            @if($dosens->onFirstPage())
                                <span class="page-link">&lsaquo;</span>
                            @else
                                <a
                                    class="page-link"
                                    href="{{ $dosens->previousPageUrl() }}"
                                    rel="prev"
                                    aria-label="Halaman sebelumnya">
                                    &lsaquo;
                                </a>
                            @endif
                        </li>

                        @foreach($pages as $page)
                            @if($page === 'ellipsis-left' || $page === 'ellipsis-right')
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            @else
                                <li class="page-item {{ $currentPage === $page ? 'active' : '' }}">
                                    @if($currentPage === $page)
                                        <span class="page-link">{{ $page }}</span>
                                    @else
                                        <a
                                            class="page-link"
                                            href="{{ $dosens->url($page) }}">
                                            {{ $page }}
                                        </a>
                                    @endif
                                </li>
                            @endif
                        @endforeach

                        <li class="page-item {{ $dosens->hasMorePages() ? '' : 'disabled' }}">
                            @if($dosens->hasMorePages())
                                <a
                                    class="page-link"
                                    href="{{ $dosens->nextPageUrl() }}"
                                    rel="next"
                                    aria-label="Halaman berikutnya">
                                    &rsaquo;
                                </a>
                            @else
                                <span class="page-link">&rsaquo;</span>
                            @endif
                        </li>
                    </ul>
                </nav>
            @endif
        </div>
    @endif
</section>
@endsection
