@php
    $filterQueryDiterapkan = collect(request()->query())
        ->reject(function ($value, $key) {
            if ($value === null || $value === '') {
                return true;
            }

            $key = (string) $key;

            if (in_array($key, ['page', '_token'], true) || str_starts_with($key, 'page_')) {
                return true;
            }

            return false;
        });

    $hasFilterDiterapkan = $filterQueryDiterapkan->isNotEmpty();

    $rawPeriodeFilter = request()->query('periode', request()->query('mode'));
    $rawPeriodeFilter = $rawPeriodeFilter === 'tahunan' ? 'tahun' : $rawPeriodeFilter;

    $tahunFilter = request()->query('tahun');
    $triwulanFilter = request()->query('triwulan');
    $semesterFilter = request()->query('semester');
    $searchFilter = trim((string) request()->query('search', ''));
    $kategoriDetailFilter = request()->query('kategori_detail');

    $filterParts = [];

    if ($rawPeriodeFilter === 'triwulan') {
        $filterParts[] = 'Triwulan ' . ($triwulanFilter ?: '-');
    } elseif ($rawPeriodeFilter === 'semester') {
        $filterParts[] = 'Semester ' . ($semesterFilter ?: '-');
    } elseif ($rawPeriodeFilter === 'tahun') {
        $filterParts[] = 'Tahunan';
    }

    if (!empty($tahunFilter)) {
        $filterParts[] = 'Tahun ' . $tahunFilter;
    }

    if ($searchFilter !== '') {
        $filterParts[] = 'Pencarian: “' . \Illuminate\Support\Str::limit($searchFilter, 28) . '”';
    }

    if (!empty($kategoriDetailFilter)) {
        $filterParts[] = 'Kategori: ' . $kategoriDetailFilter;
    }

    if (empty($filterParts) && $hasFilterDiterapkan) {
        $filterParts[] = 'Filter aktif';
    }
@endphp

@if($hasFilterDiterapkan)
    @once
        <style>
            .filter-applied-box {
                width: fit-content;
                max-width: 100%;
                display: inline-flex;
                align-items: center;
                justify-content: flex-end;
                gap: 8px;
                margin-left: auto;
                margin-bottom: 8px;
                padding: 7px 10px;
                border: 1px solid #BFDBFE;
                border-radius: 999px;
                background: linear-gradient(135deg, #EFF6FF 0%, #F8FBFF 100%);
                color: #2563EB;
                box-shadow: 0 6px 14px rgba(37, 99, 235, 0.08);
                font-size: 11px;
                font-weight: 800;
                line-height: 1.2;
                text-align: right;
            }

            .filter-applied-box i {
                font-size: 12px;
            }

            .filter-applied-label {
                color: #64748B;
                font-weight: 900;
                text-transform: uppercase;
                letter-spacing: .02em;
            }

            .filter-applied-value {
                color: #1D4ED8;
                font-weight: 900;
            }

            .filter-applied-reset {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding-left: 8px;
                border-left: 1px solid #BFDBFE;
                color: #64748B;
                font-weight: 900;
                text-decoration: none;
            }

            .filter-applied-reset:hover {
                color: #1D4ED8;
            }
        </style>
    @endonce

    <div class="filter-applied-box">
        <i class="bi bi-funnel-fill"></i>
        <span class="filter-applied-label">Filter</span>
        <span class="filter-applied-value">{{ implode(' | ', $filterParts) }}</span>
        <a href="{{ url()->current() }}" class="filter-applied-reset" title="Reset filter">
            Reset
        </a>
    </div>
@endif
