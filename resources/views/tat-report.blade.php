@extends('layouts.app')
@section('title', 'TAT Report')
@section('content')

<style>
    .report-table th, .report-table td {
        border: 1px solid #cbd5e1;
        padding: 4px 8px;
    }
    .report-table thead {
        background: #fff;
        color: #000;
    }
    .report-table tbody tr:nth-child(even) {
        background: #f1f5f9;
    }
    .hospital-logo {
        max-height: 70px;
        max-width: 120px;
        object-fit: contain;
    }

    /* ============================================================
       PRINT — clean A4 landscape, repeats header, no row splits
       ============================================================ */
    @media print {
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        body {
            background: white !important;
            font-size: 10pt;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .no-print { display: none !important; }

        #report-container {
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
        }

        #report-container .bg-white {
            box-shadow: none !important;
            border: none !important;
            border-radius: 0 !important;
            padding: 0 !important;
        }

        .report-table {
            width: 100% !important;
            font-size: 8pt !important;
        }

        /* Repeat header on every page */
        .report-table thead {
            display: table-header-group;
        }

        /* Don't split a row across pages */
        .report-table tbody tr {
            page-break-inside: avoid;
        }

        .report-table th,
        .report-table td {
            padding: 2px 4px !important;
            border: 1px solid #94a3b8 !important;
        }

        .report-table thead th {
            background: #fff !important;
            color: #000 !important;
        }

        /* Hide the Print button area in print */
        .print-hint { display: none !important; }
    }
    @media print {
    @page { size: A4 landscape; margin: 8mm; }

    /* ---- Hide everything by default ---- */
    body * {
        visibility: hidden !important;
    }

    /* ---- Then reveal ONLY the report container and its children ---- */
    #report-container,
    #report-container * {
        visibility: visible !important;
    }

    /* ---- Pull the report to the top-left of the page ---- */
    #report-container {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    /* ---- Cleanup backgrounds/borders on the report wrapper ---- */
    #report-container .bg-white {
        box-shadow: none !important;
        border: none !important;
        border-radius: 0 !important;
        padding: 0 !important;
    }

    /* ---- Keep colors when printing ---- */
    body {
        background: white !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* ---- Table polish ---- */
    .report-table { width: 100% !important; font-size: 8pt !important; }
    .report-table thead { display: table-header-group; }
    .report-table tbody tr { page-break-inside: avoid; }
    .report-table th,
    .report-table td {
        padding: 2px 4px !important;
        border: 1px solid #94a3b8 !important;
    }
    .report-table thead th {
        background: #1e293b !important;
        color: #ffffff !important;
    }

    /* ---- Explicitly hide UI chrome ---- */
    .no-print,
    nav, aside, header, footer,
    .sidebar, .navbar, .topbar { 
        display: none !important; 
    }
}
</style>

<div class="no-print max-w-full mx-auto px-4 py-6">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
        <h1 class="text-2xl font-bold text-slate-800 mb-1">STARLIS — TAT Report</h1>
        <p class="text-sm text-slate-500 mb-6">Medtech Received-Releasing Turnaround Time Analysis</p>

        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Date From</label>
                <input type="datetime-local" id="date_from"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Date To</label>
                <input type="datetime-local" id="date_to"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Test Group</label>
                <select id="tg_code" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                    <option value="%">ALL</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Patient Type</label>
                <select id="pt_code" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                    <option value="%">ALL</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Source</label>
                <select id="st_id" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                    <option value="%">ALL</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Released By</label>
                <select id="ua_user_id" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                    <option value="%">ALL</option>
                </select>
            </div>
        </div>

        <div class="flex flex-wrap gap-3 mt-6">
            <button id="btn-generate"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2.5 rounded-lg">
                ⚡ Generate Report
            </button>
            <button id="btn-print"
                    class="bg-red-400 hover:bg-red-600 text-white text-sm font-semibold px-5 py-2.5 rounded-lg">
                🖨️ Print / Save as PDF
            </button>
            <button id="btn-excel"
                    class="bg-green-700 hover:bg-green-800 text-white text-sm font-semibold px-5 py-2.5 rounded-lg">
                📊 Export Excel
            </button>
        </div>

        <p class="print-hint text-xs text-slate-500 mt-3">
            💡 Tip: For best results with large reports, click <strong>Print / Save as PDF</strong> and
            choose <em>"Save as PDF"</em> as the destination. Enable <em>"Background graphics"</em>.
        </p>
    </div>
</div>


<div id="report-container" class="max-w-full px-4 pb-12">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">

        <!-- HEADER WITH LOGO -->
        <div class="text-center mb-4">
            <div style="display:flex; align-items:center; justify-content:center; gap:16px;">
                <img src="/images/acebaliwag.png" alt="Hospital Logo" class="hospital-logo"
                     onerror="this.style.display='none'">
                <div>
                    <h2 class="text-lg font-bold text-slate-800" id="report-title">
                        Received-Releasing TAT - Date Requested by: ALL
                    </h2>
                    <p class="text-sm text-slate-700 mt-1">
                        <strong>Laboratory:</strong> ACE MEDICAL CENTER - BALIWAG
                    </p>
                </div>
            </div>
            <p class="text-sm text-slate-700 mt-2">
                <strong>Coverage:</strong> <span id="coverage-range">—</span>
            </p>
            <p class="text-sm text-slate-700 mt-1">
                <strong>Generated by:</strong> <span id="generated-by">Medical Laboratory</span>
                &nbsp;&nbsp;<strong>Date:</strong> <span id="generated-at">—</span>
            </p>
        </div>

        <div style="border-top: 2px dashed #94a3b8; margin: 16px 0;"></div>

        <!-- SUMMARY -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4 text-sm text-slate-700">
            <div class="space-y-1">
                <div><strong>% Achieved:</strong> <span id="sum-achieved-pct" class="font-bold text-emerald-700">—</span></div>
                <div><strong>Min. TAT:</strong> <span id="sum-min">—</span></div>
                <div><strong>Max. TAT:</strong> <span id="sum-max">—</span></div>
                <div><strong>Avg. TAT:</strong> <span id="sum-avg">—</span></div>
            </div>
            <div class="md:text-right space-y-1">
                <div class="font-bold text-slate-900" id="sum-group-label">ALL</div>
                <div><strong>Record Count:</strong> <span id="sum-records">—</span></div>
                <div><strong>Achieved TAT Count:</strong> <span id="sum-achieved" class="text-emerald-700 font-semibold">—</span></div>
                <div><strong>Exceeded TAT Count:</strong> <span id="sum-exceeded" class="text-rose-700 font-semibold">—</span></div>
            </div>
        </div>

        <div style="border-top: 2px dashed #94a3b8; margin: 16px 0;"></div>

        <!-- TABLE -->
        <div class="overflow-x-auto">
            <table class="report-table w-full text-xs">
                <thead>
                    <tr>
                        <th class="text-left">#</th>
                        <th class="text-left">Patient ID</th>
                        <th class="text-left">Patient</th>
                        <th class="text-left">Specimen ID</th>
                        <th class="text-left">Test</th>
                        <th class="text-left">DT Received</th>
                         <th class="text-left">DT Collected</th>   
                        <th class="text-left">DT Released</th>
                        <th class="text-left">Priority</th>
                        <th class="text-right">Target TAT</th>
                        <th class="text-right">Actual TAT</th>
                    </tr>
                </thead>
                <tbody id="report-tbody">
                    <tr>
                        <td colspan="11" class="text-center text-slate-400 py-8">
                            Click <strong>Generate Report</strong> to load data.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div style="border-top: 2px dashed #94a3b8; margin: 16px 0;"></div>
        <div class="text-xs text-slate-500 text-center">*** End of Report ***</div>
    </div>
</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
const API_BASE = '/api/tat-report';
const LAB_NAME = 'ACE MEDICAL CENTER - BALIWAG';

let reportData = [];
const $ = (id) => document.getElementById(id);

/* ============================================================
   INIT
   ============================================================ */
window.addEventListener('DOMContentLoaded', async () => {
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    $('date_from').value = `${yyyy}-${mm}-${dd}T00:00`;
    $('date_to').value   = `${yyyy}-${mm}-${dd}T23:59`;
    await loadFilters();
});

async function loadFilters() {
    try {
        const res = await fetch(`${API_BASE}/filters`);
        const data = await res.json();
        fillSelect('tg_code',    data.test_groups,   'tg_code',    'tg_name');
        fillSelect('pt_code',    data.patient_types, 'pt_code',    'pt_description');
        fillSelect('st_id',      data.sources,       'st_id',      'st_name');
        fillSelect('ua_user_id', data.users,         'ua_user_id', 'ua_user_name');
    } catch (err) { console.error(err); }
}

function fillSelect(id, items, valueKey, labelKey) {
    const sel = $(id);
    if (!items) return;
    items.forEach(item => {
        const opt = document.createElement('option');
        opt.value = item[valueKey];
        opt.textContent = item[labelKey];
        sel.appendChild(opt);
    });
}

/* ============================================================
   GENERATE
   ============================================================ */
$('btn-generate').addEventListener('click', async () => {
    const params = new URLSearchParams({
        date_from:  formatDate($('date_from').value, false),
        date_to:    formatDate($('date_to').value, true),
        tg_code:    $('tg_code').value,
        pt_code:    $('pt_code').value,
        st_id:      $('st_id').value,
        ua_user_id: $('ua_user_id').value,
    });

    $('report-tbody').innerHTML =
        '<tr><td colspan="11" class="text-center text-slate-400 py-8">Loading…</td></tr>';

    try {
        const res = await fetch(`${API_BASE}?${params}`);
        const json = await res.json();
        reportData = json.data || [];
        renderSummary(json);
        renderTable(reportData);
    } catch (err) {
        $('report-tbody').innerHTML =
            `<tr><td colspan="11" class="text-center text-rose-500 py-8">Failed: ${err.message}</td></tr>`;
    }
});

function renderSummary(json) {
    const s = json.summary || {};
    const f = json.filters || {};

    $('sum-achieved-pct').textContent = (s.percent_achieved ?? 0) + ' %';
    $('sum-min').textContent = s.min_tat_hours ?? '—';
    $('sum-max').textContent = s.max_tat_hours ?? '—';
    $('sum-avg').textContent = s.avg_tat_hours ?? '—';
    $('sum-records').textContent  = s.record_count ?? 0;
    $('sum-achieved').textContent = s.achieved_tat_count ?? 0;
    $('sum-exceeded').textContent = s.exceeded_tat_count ?? 0;

    $('coverage-range').textContent = `${f.date_from ?? '—'}  -  ${f.date_to ?? '—'}`;
    $('generated-at').textContent   = json.generated_at ?? '—';

    const tgSel = $('tg_code');
    $('sum-group-label').textContent =
        tgSel.options[tgSel.selectedIndex]?.textContent || 'ALL';

    $('report-title').textContent =
        `Received-Releasing TAT - Date Requested by: ${
            $('ua_user_id').options[$('ua_user_id').selectedIndex]?.textContent || 'ALL'
        }`;
}

function renderTable(rows) {
    const tbody = $('report-tbody');
    if (!rows.length) {
        tbody.innerHTML =
            '<tr><td colspan="11" class="text-center text-slate-400 py-8">No records found.</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map((r, i) => {
        const color = r.tat_status === 'EXCEEDED'
            ? 'text-rose-700 font-semibold'
            : r.tat_status === 'WITHIN TAT' ? 'text-emerald-700' : 'text-slate-500';
        return `<tr>
            <td>${i + 1}</td>
            <td>${escapeHtml(r.patient_id)}</td>
            <td>${escapeHtml(r.patient_name)}</td>
            <td>${escapeHtml(r.specimen_id)}</td>
            <td>${escapeHtml(r.test_code)}</td>
            <td>${fmtDate(r.collected_date)}</td>
            <td>${fmtDate(r.checkin_date)}</td>
            <td>${fmtDate(r.release_date)}</td>
            <td>${escapeHtml(r.priority)}</td>
            <td class="text-right">${fmtNumber(r.target_hours)}</td>
            <td class="text-right ${color}">${fmtNumber(r.actual_hours)}</td>
        </tr>`;
    }).join('');
}

/* ============================================================
   PRINT  (native — fast, handles thousands of rows)
   ============================================================ */
$('btn-print').addEventListener('click', () => {
    if (!reportData.length) {
        alert('No data to print. Click "Generate Report" first.');
        return;
    }
    window.print();
});

/* ============================================================
   EXCEL EXPORT
   ============================================================ */
$('btn-excel').addEventListener('click', () => {
    if (!reportData.length) {
        alert('No data to export. Click "Generate Report" first.');
        return;
    }

    const aoa = [];
    aoa.push([LAB_NAME]);
    aoa.push(['STARLIS — Received-Releasing TAT Report']);
    aoa.push([]);
    aoa.push(['Coverage:', $('coverage-range').textContent]);
    aoa.push(['Generated:', $('generated-at').textContent]);
    aoa.push(['Test Group:', $('sum-group-label').textContent]);
    aoa.push([]);
    aoa.push(['% Achieved',  $('sum-achieved-pct').textContent]);
    aoa.push(['Min. TAT',    $('sum-min').textContent]);
    aoa.push(['Max. TAT',    $('sum-max').textContent]);
    aoa.push(['Avg. TAT',    $('sum-avg').textContent]);
    aoa.push(['Record Count',  $('sum-records').textContent]);
    aoa.push(['Achieved TAT Count', $('sum-achieved').textContent]);
    aoa.push(['Exceeded TAT Count', $('sum-exceeded').textContent]);
    aoa.push([]);
    aoa.push(['#','Patient ID','Patient','Specimen ID','Test',
              'DT Collected','DT Received','DT Released','Priority',
              'Target TAT','Actual TAT','Status']);

    reportData.forEach((r, i) => {
        aoa.push([
            i + 1,
            r.patient_id,
            r.patient_name,
            r.specimen_id,
            r.test_code,
            fmtDate(r.collected_date),
            fmtDate(r.checkin_date),
            fmtDate(r.release_date),
            r.priority,
            r.target_hours,
            r.actual_hours,
            r.tat_status
        ]);
    });

    const ws = XLSX.utils.aoa_to_sheet(aoa);
    ws['!cols'] = [
        {wch:5},{wch:14},{wch:26},{wch:16},{wch:12},
        {wch:20},{wch:20},{wch:20},{wch:10},{wch:11},{wch:11},{wch:12}
    ];
    ws['!merges'] = [
        { s:{r:0,c:0}, e:{r:0,c:11} },
        { s:{r:1,c:0}, e:{r:1,c:11} },
    ];

    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'TAT Report');
    XLSX.writeFile(wb, `TATRPT_${new Date().toISOString().slice(0,10)}.xlsx`);
});

/* ============================================================
   HELPERS
   ============================================================ */
function formatDate(val, endOfDay) {
    if (!val) return '';
    const [d, t] = val.split('T');
    return `${d} ${t}:${endOfDay ? '59' : '00'}`;
}
function fmtDate(v) {
    if (!v) return '';
    return new Date(v).toLocaleString('en-US', {
        year:'numeric', month:'2-digit', day:'2-digit',
        hour:'2-digit', minute:'2-digit', hour12:true
    });
}
function fmtNumber(v) {
    if (v === null || v === undefined || v === '') return '';
    return Number(v).toFixed(2);
}
function escapeHtml(s) {
    return String(s ?? '')
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>

@endsection