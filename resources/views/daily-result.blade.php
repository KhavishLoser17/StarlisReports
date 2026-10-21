@extends('layouts.app')
@section('title', 'Daily Result Summary Report')

@section('content')

<style>
    /* ============================================================
       PRINT STYLES
       ============================================================ */
    @media print {
        @page {
            size: A4 landscape;
            margin: 8mm;
        }
        body { background: #fff !important; }
        .no-print { display: none !important; }
        .rpt-table th, .rpt-table td {
            font-size: 8px !important;
            padding: 2px 3px !important;
        }
        .pagination-bar { display: none !important; }
        .table-scroll { overflow: visible !important; max-height: none !important; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
    }

    
    .rpt-table {
        width: 100%;
        border-collapse: collapse;
    }
    .rpt-table th, .rpt-table td {
        border: 1px solid #cbd5e1;
        padding: 4px 6px;
        font-size: 11px;
        white-space: nowrap;
    }
    .rpt-table thead th {
        background: #fff;
        color: #000;
        font-weight: 600;
        text-align: center;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .rpt-table tbody tr:nth-child(even) { background: #f1f5f9; }
    .rpt-table tbody tr:hover { background: #e0f2fe; }

    /* Sticky first 4 columns */
    .rpt-table .sticky-col {
        position: sticky;
        left: 0;
        background: #fff;
        z-index: 5;
        box-shadow: 2px 0 3px rgba(0,0,0,0.05);
    }
    .rpt-table thead .sticky-col {
        background: #fff;
        z-index: 15;
        color:#000;
    }
    .rpt-table .sticky-col-2 { left: 60px; }
    .rpt-table .sticky-col-3 { left: 220px; }
    .rpt-table .sticky-col-4 { left: 380px; }

    /* Scroll wrapper — max height with sticky header */
    .table-scroll {
        max-height: 65vh;
        overflow: auto;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
    }

    /* Pagination bar */
    .pagination-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 12px;
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-top: none;
        border-radius: 0 0 6px 6px;
        font-size: 13px;
        color: #475569;
    }
    .pagination-bar button {
        padding: 4px 12px;
        border: 1px solid #cbd5e1;
        background: #fff;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
    }
    .pagination-bar button:hover:not(:disabled) {
        background: #eff6ff;
        border-color: #3b82f6;
        color: #1d4ed8;
    }
    .pagination-bar button:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }
    .pagination-bar select {
        padding: 3px 6px;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        font-size: 12px;
    }
</style>

<!-- ============ CONTROL BAR ============ -->
<div class="no-print max-w-full mx-auto mb-4 bg-white rounded-xl shadow p-4">
    <div class="flex flex-wrap gap-3 items-end">
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-slate-800 mb-1">STARLIS — Daily Result Summary</h1>
            <p class="text-sm text-slate-500">Daily Result Summary Report</p>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Date From</label>
            <input type="date" id="date_from" class="border rounded px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Date To</label>
            <input type="date" id="date_to" class="border rounded px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Test Group</label>
            <select id="tg_code" class="border rounded px-3 py-2 text-sm">
                <option value="%">ALL</option>
            </select>
        </div>
        <button id="btn-load" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-semibold">
            Generate
        </button>
        {{-- <button id="btn-pdf" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded text-sm font-semibold">
            Export PDF
        </button> --}}
        <button id="btn-csv" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded text-sm font-semibold">
            Export CSV
        </button>
    </div>
</div>

<!-- ============ REPORT ============ -->
<div id="report-container" class="max-w-full mx-auto bg-white rounded-xl shadow p-6">

    <h1 class="text-2xl font-bold text-slate-800 mb-4">LABORATORY RESULT SUMMARY REPORT</h1>

    <div class="grid grid-cols-2 gap-2 text-sm mb-4">
        <div><strong>Test Group :</strong> <span id="rpt-group">—</span></div>
        <div class="text-right"><strong>Printed Date :</strong> <span id="rpt-printed">—</span></div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">🔍 Search Patient / Lab No</label>
            <div class="relative">
                <input type="text" id="search" placeholder="Type name or lab no…"
                    class="border rounded px-3 py-2 pr-8 text-sm w-64 focus:ring-2 focus:ring-blue-500">
                <button id="btn-clear-search" type="button"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-600 hidden">
                    ✕
                </button>
            </div>
        </div>
        <div><strong>Filter Date :</strong> <span id="rpt-range">—</span></div>
        <div class="text-right"><strong>Page :</strong> <span id="rpt-page-label">1 of 1</span></div>
    </div>

    <hr class="border-slate-800 mb-4">

    <!-- Scroll wrapper with sticky header + sticky left columns -->
    <div class="table-scroll">
        <table class="rpt-table" id="rpt-table">
            <thead id="rpt-thead"></thead>
            <tbody id="rpt-tbody">
                <tr><td colspan="20" class="text-center py-6 text-slate-400">Click Generate to load data.</td></tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination Bar (hidden in print) -->
    <div class="pagination-bar no-print" id="pagination-bar">
        <div class="flex items-center gap-3">
            <span>Rows per page:</span>
            <select id="page-size">
                <option value="25">25</option>
                <option value="50" selected>50</option>
                <option value="100">100</option>
                <option value="0">All</option>
            </select>
            <span id="page-info">0 records</span>
        </div>
        <div class="flex items-center gap-2">
            <button id="btn-first" title="First">« First</button>
            <button id="btn-prev">‹ Prev</button>
            <span id="page-numbers" class="px-2"></span>
            <button id="btn-next">Next ›</button>
            <button id="btn-last" title="Last">Last »</button>
        </div>
    </div>

</div>

<script>
const API = '/api/daily-result-summary';
const $ = (id) => document.getElementById(id);

// ============================================================
// STATE
// ============================================================
let currentRows   = [];   // all rows from API
let filteredRows  = [];   // rows after search filter
let currentCols   = [];   // test columns (dynamic)
let currentPage   = 1;
let pageSize      = 50;
let searchTerm    = '';

// ============================================================
// INIT
// ============================================================
window.addEventListener('DOMContentLoaded', () => {
    const today = new Date().toISOString().slice(0, 10);
    $('date_from').value = today;
    $('date_to').value   = today;
    loadGroups();
    attachSearchHandlers();
});

async function loadGroups() {
    try {
        const res = await fetch('/api/tat-report/filters');
        const data = await res.json();
        data.test_groups.forEach(g => {
            const opt = document.createElement('option');
            opt.value = g.tg_code;
            opt.textContent = g.tg_name;
            $('tg_code').appendChild(opt);
        });
    } catch (e) { console.error(e); }
}

// ============================================================
// SEARCH — live filter as you type
// ============================================================
function attachSearchHandlers() {
    const searchInput = $('search');
    const clearBtn    = $('btn-clear-search');
    if (!searchInput) return;

    searchInput.addEventListener('input', (e) => {
        searchTerm = e.target.value.trim().toLowerCase();
        clearBtn.classList.toggle('hidden', !searchTerm);
        currentPage = 1;
        applyFilter();
        renderTable();
    });

    clearBtn.addEventListener('click', () => {
        searchInput.value = '';
        searchTerm = '';
        clearBtn.classList.add('hidden');
        currentPage = 1;
        applyFilter();
        renderTable();
    });
}

function applyFilter() {
    if (!searchTerm) {
        filteredRows = currentRows;
        return;
    }
    filteredRows = currentRows.filter(r => {
        const name  = (r.patient_name || '').toLowerCase();
        const lab   = String(r.lab_no || '').toLowerCase();
        const relBy = (r.released_by || '').toLowerCase();
        return name.includes(searchTerm)
            || lab.includes(searchTerm)
            || relBy.includes(searchTerm);
    });
}
const formatDate = (date) => {
    if (!date) return '—';

    const [year, month, day] = date.slice(0, 10).split('-');

    return new Date(year, month - 1, day).toLocaleDateString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric'
    });
};

// ============================================================
// GENERATE
// ============================================================
$('btn-load').addEventListener('click', async () => {
    const params = new URLSearchParams({
        date_from: $('date_from').value + ' 00:00:00',
        date_to:   $('date_to').value   + ' 23:59:59',
        tg_code:   $('tg_code').value,
    });

    $('rpt-tbody').innerHTML = '<tr><td colspan="20" class="text-center py-6 text-slate-400">Loading…</td></tr>';

    try {
        const res  = await fetch(`${API}?${params}`);
        const json = await res.json();

        currentRows  = json.data || [];
        currentCols  = json.test_columns || [];
        filteredRows = currentRows;   // initialize filtered
        currentPage  = 1;

        // Reset search
        searchTerm = '';
        if ($('search')) $('search').value = '';
        if ($('btn-clear-search')) $('btn-clear-search').classList.add('hidden');

        renderHeader(json);
        renderTable();
    } catch (err) {
        console.error(err);
        $('rpt-tbody').innerHTML = `<tr><td colspan="20" class="text-center py-6 text-rose-500">Failed: ${err.message}</td></tr>`;
    }
});

function renderHeader(json) {
    const f = json.filters || {};
    $('rpt-group').textContent   = $('tg_code').selectedOptions[0].textContent;
    $('rpt-range').textContent =`${formatDate(f.date_from)} to ${formatDate(f.date_to)}`;
    $('rpt-printed').textContent = new Date().toLocaleDateString('en-US', { year:'numeric', month:'long', day:'numeric' });
}


// ============================================================
// RENDER TABLE (with pagination)
// ============================================================
function renderTable() {
    const baseCols = [
        { key: 'lab_no',       label: 'Laboratory',   align: 'left',   sticky: 'sticky-col' },
        { key: 'patient_name', label: 'Patient Name', align: 'left',   sticky: 'sticky-col sticky-col-2' },
        { key: 'released_dt',  label: 'Released DT',  align: 'left',   sticky: 'sticky-col sticky-col-3' },
        { key: 'test_group',   label: 'Test Group',   align: 'left',   sticky: 'sticky-col sticky-col-4' },
    ];
    const testCols = currentCols.map(t => ({ key: t, label: t, align: 'center', sticky: '' }));
    const allCols  = [...baseCols, ...testCols];

    // -- Header --
    $('rpt-thead').innerHTML = `<tr>${allCols.map(c =>
        `<th class="${c.sticky} text-${c.align}">${c.label}</th>`
    ).join('')}</tr>`;

    // -- Paginate using FILTERED rows --
    const totalRows  = filteredRows.length;
    const totalPages = pageSize === 0 ? 1 : Math.ceil(totalRows / pageSize);

    if (currentPage > totalPages) currentPage = totalPages || 1;

    const start = pageSize === 0 ? 0 : (currentPage - 1) * pageSize;
    const end   = pageSize === 0 ? totalRows : Math.min(start + pageSize, totalRows);
    const pageRows = filteredRows.slice(start, end);

    // -- Body --
    if (!pageRows.length) {
        $('rpt-tbody').innerHTML = `<tr><td colspan="${allCols.length}" class="text-center py-6 text-slate-400">
            ${searchTerm ? `No records match "${searchTerm}".` : 'No records found.'}
        </td></tr>`;
    } else {
        $('rpt-tbody').innerHTML = pageRows.map(r => {
            const cells = allCols.map(c => {
                let val = r[c.key];
                if (c.key === 'released_dt' && val) {
                    val = new Date(val).toLocaleString('en-US', {
                        year: 'numeric', month: 'short', day: '2-digit',
                        hour: '2-digit', minute: '2-digit', hour12: true
                    });
                }
                return `<td class="${c.sticky} text-${c.align}">${val ?? ''}</td>`;
            }).join('');
            return `<tr>${cells}</tr>`;
        }).join('');
    }

    // -- Pagination info --
    $('page-info').textContent = totalRows
        ? `Showing ${start + 1}–${end} of ${totalRows} records${searchTerm ? ' (filtered)' : ''}`
        : `0 records`;

    $('page-numbers').textContent = pageSize === 0
        ? `Page 1 of 1`
        : `Page ${currentPage} of ${totalPages}`;

    $('rpt-page-label').textContent = pageSize === 0
        ? `1 of 1`
        : `${currentPage} of ${totalPages}`;

    // Disable buttons
    $('btn-first').disabled = currentPage === 1 || pageSize === 0;
    $('btn-prev').disabled  = currentPage === 1 || pageSize === 0;
    $('btn-next').disabled  = currentPage >= totalPages || pageSize === 0;
    $('btn-last').disabled  = currentPage >= totalPages || pageSize === 0;

    window._totalPages = totalPages;
}

// ============================================================
// PAGINATION CONTROLS
// ============================================================
$('page-size').addEventListener('change', (e) => {
    pageSize = parseInt(e.target.value, 10);
    currentPage = 1;
    renderTable();
});

$('btn-first').addEventListener('click', () => { currentPage = 1; renderTable(); });
$('btn-prev').addEventListener('click',  () => { currentPage = Math.max(1, currentPage - 1); renderTable(); });
$('btn-next').addEventListener('click',  () => { currentPage = Math.min(window._totalPages, currentPage + 1); renderTable(); });
$('btn-last').addEventListener('click',  () => { currentPage = window._totalPages; renderTable(); });

// ============================================================
// PDF EXPORT — exports FILTERED rows (respects search)
// ============================================================
// $('btn-pdf').addEventListener('click', async () => {
//     const rowsToExport = filteredRows.length ? filteredRows : currentRows;
//     if (!rowsToExport.length) { alert('No data to export. Click Generate first.'); return; }

//     // Temporarily render all rows for PDF
//     const savedPageSize = pageSize;
//     const savedPage     = currentPage;

//     pageSize    = 0;
//     currentPage = 1;
//     renderTable();

//     await new Promise(r => setTimeout(r, 300));

//     const opt = {
//         margin:      [5, 5, 5, 5],
//         filename:    `DailyResultSummary_${$('date_from').value}${searchTerm ? '_filtered' : ''}.pdf`,
//         image:       { type: 'jpeg', quality: 0.98 },
//         html2canvas: { scale: 2, useCORS: true, scrollY: 0, windowWidth: 1600 },
//         jsPDF:       { unit: 'mm', format: 'a4', orientation: 'landscape' },
//         pagebreak:   { mode: ['css', 'legacy'] }
//     };

//     await html2pdf().set(opt).from($('report-container')).save();

//     // Restore pagination
//     pageSize    = savedPageSize;
//     currentPage = savedPage;
//     renderTable();
// });

// ============================================================
// CSV EXPORT — exports FILTERED rows (respects search)
// ============================================================
$('btn-csv').addEventListener('click', () => {
    const rowsToExport = filteredRows.length ? filteredRows : currentRows;
    if (!rowsToExport.length) { alert('No data to export. Click Generate first.'); return; }

    const headers = [
        'Laboratory', 'Patient Name', 'Gender', 'Age', 'DOB',
        'Released Date & Time', 'Released By', 'Test Group',
        ...currentCols
    ];

    const rows = rowsToExport.map(r => [
        r.lab_no,
        r.patient_name,
        r.gender,
        r.age,
        r.dob,
        r.released_dt,
        r.released_by,
        r.test_group,
        ...currentCols.map(c => r[c] ?? '')
    ]);

    const csv = [headers, ...rows]
        .map(row => row.map(v => `"${String(v ?? '').replace(/"/g, '""')}"`).join(','))
        .join('\r\n');

    const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = `DailyResultSummary_${$('date_from').value}${searchTerm ? '_filtered' : ''}.csv`;
    a.click();
    URL.revokeObjectURL(a.href);
});
</script>

@endsection