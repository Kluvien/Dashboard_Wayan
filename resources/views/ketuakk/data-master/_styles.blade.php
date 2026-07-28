<style>
    .ketuakk-master { border:1px solid #E2E8F0; border-radius:14px; background:#FFF; overflow:hidden; margin-bottom:24px; }
    .ketuakk-master__header { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; padding:20px 22px; border-bottom:1px solid #E2E8F0; flex-wrap:wrap; }
    .ketuakk-master__eyebrow { color:#2563EB; font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:5px; }
    .ketuakk-master__title { color:#0F172A; font-size:22px; font-weight:700; letter-spacing:-.02em; margin:0; }
    .ketuakk-master__description { color:#64748B; font-size:13px; line-height:1.55; margin:5px 0 0; }
    .ketuakk-master__toolbar { padding:16px 22px; border-bottom:1px solid #EEF2F7; background:#F8FAFC; }
    .ketuakk-master__search { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
    .ketuakk-master__search .form-control { min-height:40px; flex:1 1 360px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px; }
    .ketuakk-master__button { min-height:38px; display:inline-flex; align-items:center; justify-content:center; gap:6px; padding:0 13px; border:1px solid #CBD5E1; border-radius:8px; background:#FFF; color:#334155; font-size:12px; font-weight:700; text-decoration:none; white-space:nowrap; }
    .ketuakk-master__button:hover { background:#F8FAFC; color:#0F172A; }
    .ketuakk-master__button--primary { border-color:#2563EB; background:#2563EB; color:#FFF; }
    .ketuakk-master__button--primary:hover { background:#1D4ED8; color:#FFF; }
    .ketuakk-master__button--danger { border-color:#FCA5A5; color:#B91C1C; }
    .ketuakk-master__button--danger:hover { background:#FEF2F2; color:#991B1B; }
    .ketuakk-master__button:focus-visible { outline:3px solid rgba(37,99,235,.22); outline-offset:2px; }
    .ketuakk-master__scroll { overflow-x:auto; }
    .ketuakk-master__table { width:100%; margin:0; border:0!important; border-radius:0; }
    .ketuakk-master__table th,.ketuakk-master__table td { padding:11px 16px!important; border:0!important; border-bottom:1px solid #EEF2F7!important; color:#334155; background:#FFF; font-size:13px; vertical-align:middle; }
    .ketuakk-master__table thead th { background:#F8FAFC; color:#475569; font-size:11px; font-weight:700; letter-spacing:.03em; text-transform:uppercase; white-space:nowrap; }
    .ketuakk-master__table tbody tr:hover>td { background:#F8FAFC; }
    .ketuakk-master__table tbody tr:last-child>td { border-bottom:0!important; }
    .ketuakk-master__cell--identity { color:#0F172A!important; font-weight:700!important; white-space:normal; overflow-wrap:anywhere; }
    .ketuakk-master__cell--number { text-align:right; white-space:nowrap; font-weight:700; font-variant-numeric:tabular-nums; }
    .ketuakk-master__cell--date { text-align:center; white-space:nowrap; font-variant-numeric:tabular-nums; }
    .ketuakk-master__cell--action { white-space:nowrap; text-align:center; }
    .ketuakk-master__actions-inline { display:flex; justify-content:center; gap:7px; flex-wrap:wrap; }
    .ketuakk-master__label { display:inline-block; padding:3px 7px; border:1px solid #E2E8F0; border-radius:5px; background:#F8FAFC; color:#475569; font-size:11px; font-weight:600; }
    .ketuakk-master__empty { padding:24px 16px!important; color:#64748B!important; font-size:13px!important; font-weight:500!important; text-align:center; }
    .ketuakk-master__footer { display:flex; justify-content:space-between; align-items:center; gap:14px; padding:14px 18px; border-top:1px solid #E2E8F0; flex-wrap:wrap; }
    .ketuakk-master__form { padding:20px 22px; }
    .ketuakk-master__form-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
    .ketuakk-master__field--full { grid-column:1/-1; }
    .ketuakk-master__field label { display:block; margin-bottom:6px; color:#334155; font-size:12px; font-weight:700; }
    .ketuakk-master__field .form-control,.ketuakk-master__field .form-select { min-height:42px; border:1px solid #CBD5E1; border-radius:8px; color:#334155; font-size:13px; }
    .ketuakk-master__field .form-control:focus,.ketuakk-master__field .form-select:focus { border-color:#2563EB; box-shadow:0 0 0 3px rgba(37,99,235,.14); }
    .ketuakk-master__form-actions { display:flex; gap:8px; padding-top:18px; margin-top:4px; border-top:1px solid #EEF2F7; grid-column:1/-1; flex-wrap:wrap; }
    .ketuakk-master__validation { margin-bottom:16px; padding:12px 14px; border:1px solid #FECACA; border-radius:8px; background:#FEF2F2; color:#991B1B; font-size:13px; }
    .ketuakk-master__section-header { padding:16px 20px; border-bottom:1px solid #E2E8F0; background:#F8FAFC; }
    .ketuakk-master__section-title { margin:0; color:#0F172A; font-size:15px; font-weight:700; }
    @media(max-width:576px){ .ketuakk-master__header,.ketuakk-master__form{padding:18px 16px}.ketuakk-master__form-grid{grid-template-columns:1fr}.ketuakk-master__field--full{grid-column:auto}.ketuakk-master__button{width:100%} }
</style>
