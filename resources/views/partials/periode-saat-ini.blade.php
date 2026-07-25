@php
    $tanggalSaatIni = now();
    $triwulanSaatIni = (int) ceil($tanggalSaatIni->month / 3);
    $semesterSaatIni = $tanggalSaatIni->month <= 6 ? 1 : 2;
    $tahunSaatIni = $tanggalSaatIni->year;
@endphp

<style>
    .km-current-period-banner {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0 0 14px;
        padding: 12px 15px;
        border: 1px solid #BFDBFE;
        border-left: 4px solid #4F7DF3;
        border-radius: 14px;
        background: linear-gradient(90deg, #EFF6FF 0%, #F8FBFF 58%, #FFFFFF 100%);
        box-shadow: 0 5px 14px rgba(37, 99, 235, .06);
    }

    .km-current-period-icon {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 38px;
        border-radius: 11px;
        background: #DBEAFE;
        color: #2563EB;
        font-size: 18px;
    }

    .km-current-period-content {
        min-width: 0;
        flex: 1;
    }

    .km-current-period-title {
        color: #1E3A8A;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .03em;
        text-transform: uppercase;
    }

    .km-current-period-values {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 7px;
        margin-top: 4px;
        color: #334155;
        font-size: 14px;
        font-weight: 800;
    }

    .km-current-period-value {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border: 1px solid #D7E6FF;
        border-radius: 999px;
        background: #FFFFFF;
        color: #1D4ED8;
        line-height: 1;
        white-space: nowrap;
    }

    .km-current-period-separator {
        color: #93C5FD;
        font-weight: 900;
    }

    @media (max-width: 576px) {
        .km-current-period-banner {
            align-items: flex-start;
        }

        .km-current-period-values {
            gap: 6px;
            font-size: 13px;
        }

        .km-current-period-separator {
            display: none;
        }
    }
</style>

<div class="km-current-period-banner" role="status" aria-label="Periode saat ini">
    <div class="km-current-period-icon">
        <i class="bi bi-calendar-range"></i>
    </div>

    <div class="km-current-period-content">
        <div class="km-current-period-title">Periode Saat Ini</div>
        <div class="km-current-period-values">
            <span class="km-current-period-value">Triwulan {{ $triwulanSaatIni }}</span>
            <span class="km-current-period-separator">|</span>
            <span class="km-current-period-value">Semester {{ $semesterSaatIni }}</span>
            <span class="km-current-period-separator">|</span>
            <span class="km-current-period-value">Tahun {{ $tahunSaatIni }}</span>
        </div>
    </div>
</div>
