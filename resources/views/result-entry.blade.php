@extends('layouts.app')
@section('title', 'Patient Result Entry')

@section('content')
<style>
    .left-panel { max-height: calc(100vh - 200px); overflow-y: auto; }
    .results-grid { max-height: calc(100vh - 420px); overflow: auto; }
    .results-grid th { position: sticky; top: 0; background: #1e293b; color: #fff; z-index: 5; }
    .results-grid td { padding: 2px 6px; font-size: 12px; border-bottom: 1px solid #e2e8f0; }
    .results-grid tr.group-header { background: #dbeafe; font-weight: bold; color: #1e40af; }
    .results-grid tr.sub-item    { background: #f1f5f9; }
    .results-grid input.result-input {
        width: 90px; border: 1px solid #cbd5e1; border-radius: 3px;
        padding: 2px 4px; font-size: 12px; text-align: right;
    }
    .results-grid input.result-input:focus {
        outline: 2px solid #3b82f6; border-color: transparent;
    }
    .flag-H { background: #fef08a; }
    .flag-L { background: #bfdbfe; }
    .labno-item { cursor: pointer; }
    .labno-item:hover { background: #eff6ff; }
    .labno-item.active { background: #3b82f6; color: #fff; }
</style>

<div class="p-4 max-w-full">

    <!-- ============ TOP TOOLBAR ============ -->
    <div class="bg-white rounded-xl shadow p-3 mb-3 flex flex-wrap gap-2 items-end">
        <div>
            <label class="block text-xs font-semibold">Filter Date</label>
            <select id="filter-date" class="border rounded px-2 py-1 text-sm">
                <option value="today">Today</option>
                <option value="yesterday">Yesterday</option>
                <option value="week">This Week</option>
                <option value="custom">Custom</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold">Test Group</label>
            <select id="tg_code" class="border rounded px-2 py-1 text-sm">
                <option value="%">ALL</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold">Status</label>
            <select id="status" class="border rounded px-2 py-1 text-sm">
                <option value="%">All</option>
                <option value="N">Pending</option>
                <option value="Y">Released</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold">Name / PID</label>
            <input type="text" id="name" class="border rounded px-2 py-1 text-sm w-48">
        </div>
        <div>
            <label class="block text-xs font-semibold">Lab No. / Specimen No.</label>
            <input type="text" id="labno" class="border rounded px-2 py-1 text-sm w-48">
        </div>
        <button id="btn-search" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-3 py-1.5 rounded">
            🔍 Search
        </button>

        <div class="ml-auto flex gap-2">
            <button id="btn-save"     class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm px-3 py-1.5 rounded">💾 Save</button>
            <button id="btn-release"  class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-3 py-1.5 rounded">✅ Release</button>
            <button id="btn-history"  class="bg-slate-500 hover:bg-slate-600 text-white text-sm px-3 py-1.5 rounded">📋 History</button>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-3">

        <!-- ============ LEFT PANEL: LAB NO LIST ============ -->
        <div class="col-span-3 bg-white rounded-xl shadow p-3">
            <div class="text-xs font-semibold text-slate-500 mb-2">LAB NO. LIST</div>
            <div id="labno-list" class="left-panel border rounded">
                <table class="w-full text-xs">
                    <thead class="bg-slate-100 text-slate-600">
                        <tr>
                            <th class="text-left p-1">Laboratory No.</th>
                            <th class="text-left p-1">Patient Name</th>
                        </tr>
                    </thead>
                    <tbody id="labno-tbody">
                        <tr><td colspan="2" class="p-3 text-center text-slate-400">Loading…</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="flex justify-between items-center mt-2 text-xs text-slate-600">
                <button id="btn-prev-list" class="px-2 py-1 border rounded">‹ Prev</button>
                <span id="list-info">—</span>
                <button id="btn-next-list" class="px-2 py-1 border rounded">Next ›</button>
            </div>
        </div>

        <!-- ============ RIGHT PANEL: PATIENT + GRID ============ -->
        <div class="col-span-9 bg-white rounded-xl shadow p-4">

            <!-- Patient Header -->
            <div class="grid grid-cols-4 gap-3 text-sm mb-3">
                <div>
                    <label class="text-xs font-semibold text-slate-500">Lab. No.</label>
                    <div id="h-labno" class="font-mono font-bold text-blue-700">—</div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500">Trx No.</label>
                    <div id="h-trxno" class="font-mono">—</div>
                </div>
                <div class="col-span-2">
                    <label class="text-xs font-semibold text-slate-500">Patient Name</label>
                    <div id="h-pname" class="font-bold text-blue-800">—</div>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-500">Lab. No. DT</label>
                    <div id="h-labdate">—</div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500">Gender</label>
                    <div id="h-gender">—</div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500">Birth Date</label>
                    <div id="h-birthdate">—</div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500">Age</label>
                    <div id="h-age">—</div>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-500">Collection DT</label>
                    <div id="h-collected">—</div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500">Patient Type</label>
                    <div id="h-ptype">—</div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500">Room No.</label>
                    <div id="h-room">—</div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500">Source</label>
                    <div id="h-source">—</div>
                </div>

                <div class="col-span-4">
                    <label class="text-xs font-semibold text-slate-500">Diagnosis / Clinical Info</label>
                    <div id="h-clinical" class="border rounded px-2 py-1 bg-slate-50">—</div>
                </div>
            </div>

            <!-- Results Grid -->
            <div class="results-grid border rounded">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="text-left p-1">Test Group</th>
                            <th class="text-left p-1">Test Name</th>
                            <th class="text-center p-1">Result</th>
                            <th class="text-center p-1">AF</th>
                            <th class="text-center p-1">RF</th>
                            <th class="text-left p-1">Reference</th>
                            <th class="text-left p-1">Unit</th>
                            <th class="text-left p-1">Prev. Result</th>
                            <th class="text-left p-1">Prev. DT</th>
                            <th class="text-left p-1">1st Run</th>
                            <th class="text-left p-1">2nd Run</th>
                            <th class="text-left p-1">3rd Run</th>
                            <th class="text-left p-1">Comment</th>
                        </tr>
                    </thead>
                    <tbody id="results-tbody">
                        <tr><td colspan="13" class="text-center p-6 text-slate-400">
                            Select a Lab No. from the left panel.
                        </td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
const $ = (id) => document.getElementById(id);
let currentLabno = null;
let currentHeader = null;
let listOffset = 0;
const LIST_LIMIT = 50;

// ============================================================
// INIT
// ============================================================
window.addEventListener('DOMContentLoaded', () => {
    loadTestGroups();
    loadLabnoList();
});

async function loadTestGroups() {
    try {
        const r = await fetch('/api/results/filters');
        const d = await r.json();
        d.test_groups.forEach(g => {
            const o = document.createElement('option');
            o.value = g.tg_code;
            o.textContent = g.tg_name;
            $('tg_code').appendChild(o);
        });
    } catch (e) { console.error(e); }
}

// ============================================================
// LAB NO LIST
// ============================================================
$('btn-search').addEventListener('click', () => { listOffset = 0; loadLabnoList(); });
$('btn-prev-list').addEventListener('click', () => { if (listOffset > 0) { listOffset -= LIST_LIMIT; loadLabnoList(); } });
$('btn-next-list').addEventListener('click', () => { listOffset += LIST_LIMIT; loadLabnoList(); });

async function loadLabnoList() {
    const params = new URLSearchParams({
        date_from: getDateFrom(),
        date_to:   getDateTo(),
        tg_code:   $('tg_code').value,
        release_flag: $('status').value,
        name:      $('name').value || '%',
        labno:     $('labno').value || '%',
        offset:    listOffset,
    });

    $('labno-tbody').innerHTML = `<tr><td colspan="2" class="p-3 text-center text-slate-400">Loading…</td></tr>`;

    try {
        const r = await fetch(`/api/results/labno-list?${params}`);
        const d = await r.json();

        if (!d.rows.length) {
            $('labno-tbody').innerHTML = `<tr><td colspan="2" class="p-3 text-center text-slate-400">No records.</td></tr>`;
        } else {
            $('labno-tbody').innerHTML = d.rows.map(row => `
                <tr class="labno-item" data-labno="${row.lab_no}">
                    <td class="p-1 font-mono">${row.lab_no}</td>
                    <td class="p-1 truncate">${row.patient_name}</td>
                </tr>`).join('');

            document.querySelectorAll('.labno-item').forEach(el => {
                el.addEventListener('click', () => selectLabno(el.dataset.labno));
            });
        }

        const from = d.total ? listOffset + 1 : 0;
        const to   = Math.min(listOffset + LIST_LIMIT, d.total);
        $('list-info').textContent = `(${from} - ${to}) of ${d.total}`;

        $('btn-prev-list').disabled = listOffset === 0;
        $('btn-next-list').disabled = (listOffset + LIST_LIMIT) >= d.total;
    } catch (e) {
        console.error(e);
    }
}

function getDateFrom() {
    const mode = $('filter-date').value;
    const d = new Date();
    if (mode === 'yesterday') d.setDate(d.getDate() - 1);
    if (mode === 'week')      d.setDate(d.getDate() - 7);
    return d.toISOString().slice(0, 10);
}
function getDateTo() {
    return new Date().toISOString().slice(0, 10);
}

// ============================================================
// SELECT LABNO → LOAD DETAILS
// ============================================================
async function selectLabno(labno) {
    currentLabno = labno;
    document.querySelectorAll('.labno-item').forEach(el => {
        el.classList.toggle('active', el.dataset.labno === labno);
    });

    const tg = $('tg_code').value;
    const r  = await fetch(`/api/results/details/${labno}?tg_code=${encodeURIComponent(tg)}`);
    const d  = await r.json();

    currentHeader = d.header;
    renderHeader(d.header, d.sample);
    renderResults(d.results);
}

function renderHeader(h, s) {
    if (!h) return;
    $('h-labno').textContent     = h.lab_no;
    $('h-trxno').textContent     = h.trx_no ?? '';
    $('h-pname').textContent     = h.patient_name ?? '';
    $('h-labdate').textContent   = fmtDateTime(h.lab_date);
    $('h-gender').textContent    = h.gender ?? '';
    $('h-birthdate').textContent = fmtDate(h.birthdate);
    $('h-age').textContent       = `${h.age_year ?? 0}Y-${h.age_month ?? 0}M-${h.age_day ?? 0}D`;
    $('h-collected').textContent = fmtDateTime(s?.los_collected_on);
    $('h-ptype').textContent     = h.patient_type ?? '';
    $('h-room').textContent      = h.room_no ?? '';
    $('h-source').textContent    = h.source ?? '';
    $('h-clinical').textContent  = h.clinical_info ?? '';
}

function renderResults(rows) {
    if (!rows.length) {
        $('results-tbody').innerHTML = `<tr><td colspan="13" class="text-center p-6 text-slate-400">No tests.</td></tr>`;
        return;
    }

    let lastGroup = null;
    let html = '';

    rows.forEach((r, i) => {
        // Group header row when the group changes
        if (r.test_group !== lastGroup) {
            html += `<tr class="group-header"><td colspan="13" class="px-2 py-1">${r.test_group}</td></tr>`;
            lastGroup = r.test_group;
        }

        const isSub = r.parent_test_code && r.parent_test_code !== '0';
        const flagClass = r.result_flag === 'H' ? 'flag-H'
                        : r.result_flag === 'L' ? 'flag-L' : '';

        html += `
            <tr class="${isSub ? 'sub-item' : ''}" data-test="${r.test_code}">
                <td class="p-1">${r.test_group}</td>
                <td class="p-1 ${isSub ? 'pl-6' : ''}">${r.test_name}</td>
                <td class="text-center p-1">
                    <input type="text" class="result-input ${flagClass}"
                           value="${escapeHtml(r.result ?? '')}"
                           data-test="${r.test_code}"
                           data-field="result"
                           ${r.release_flag === 'Y' ? 'readonly' : ''}>
                </td>
                <td class="text-center p-1">${r.result_flag ?? ''}</td>
                <td class="text-center p-1">${r.release_flag ?? ''}</td>
                <td class="p-1">${r.reference ?? ''}</td>
                <td class="p-1">${r.unit ?? ''}</td>
                <td class="p-1">${r.prev_result ?? ''}</td>
                <td class="p-1">${fmtDate(r.prev_result_dt)}</td>
                <td class="p-1">${r.run1 ?? ''}</td>
                <td class="p-1">${r.run2 ?? ''}</td>
                <td class="p-1">${r.run3 ?? ''}</td>
                <td class="p-1">
                    <input type="text" class="border rounded px-1 py-0.5 text-xs w-full"
                           value="${escapeHtml(r.comment ?? '')}"
                           data-test="${r.test_code}"
                           data-field="comment"
                           ${r.release_flag === 'Y' ? 'readonly' : ''}>
                </td>
            </tr>`;
    });

    $('results-tbody').innerHTML = html;
}

// ============================================================
// SAVE
// ============================================================
$('btn-save').addEventListener('click', () => submitResults('save'));
$('btn-release').addEventListener('click', () => {
    if (!confirm('Release this lab number? Cannot be undone easily.')) return;
    submitResults('release');
});

async function submitResults(action) {
    if (!currentLabno) { alert('Select a Lab No. first.'); return; }

    // Collect only edited rows
    const results = [];
    document.querySelectorAll('#results-tbody tr[data-test]').forEach(tr => {
        const testCode = tr.dataset.test;
        const resInput = tr.querySelector(`input[data-field="result"]`);
        const cmtInput = tr.querySelector(`input[data-field="comment"]`);
        results.push({
            test_code: testCode,
            result:    resInput?.value ?? '',
            comment:   cmtInput?.value ?? '',
        });
    });

    const payload = {
        labno:          currentLabno,
        trxno:          currentHeader?.trx_no,
        global_comment: currentHeader?.global_comment ?? '',
        pathologist_id: currentHeader?.pathologist_id ?? '',
        results,
    };

    try {
        const r = await fetch(`/api/results/${action}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify(payload),
        });
        const d = await r.json();

        if (d.success) {
            alert(d.message);
            loadLabnoList();      // refresh status
        } else {
            alert('Error: ' + d.message);
        }
    } catch (e) {
        alert('Request failed: ' + e.message);
    }
}

// ============================================================
// HISTORY
// ============================================================
$('btn-history').addEventListener('click', async () => {
    if (!currentLabno) { alert('Select a Lab No. first.'); return; }
    const r = await fetch(`/api/results/history/${currentLabno}`);
    const d = await r.json();
    console.table(d.history);
    alert(`Found ${d.history.length} log entries. Check browser console for details.`);
});

// ============================================================
// HELPERS
// ============================================================
function fmtDate(v) {
    if (!v) return '';
    return new Date(v).toLocaleDateString('en-US', { year:'numeric', month:'2-digit', day:'2-digit' });
}
function fmtDateTime(v) {
    if (!v) return '';
    return new Date(v).toLocaleString('en-US', {
        year:'numeric', month:'2-digit', day:'2-digit',
        hour:'2-digit', minute:'2-digit', hour12: true
    });
}
function escapeHtml(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;')
                          .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
@endsection