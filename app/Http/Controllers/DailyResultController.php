<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DailyResultController extends Controller
{
    public function dailyResultSummary(Request $request)
{
    $dateFrom = (string) ($request->input('date_from') ?? now()->startOfDay()->toDateTimeString());
    $dateTo   = (string) ($request->input('date_to')   ?? now()->endOfDay()->toDateTimeString());
    $tgCode   = (string) ($request->input('tg_code')   ?? '%');

    // ------------------------------------------------------------
    // 1. Fetch flattened rows (same shape as the VB.NET GetResult)
    // ------------------------------------------------------------
    $query = DB::table('lab_order_hdr as loh')
        ->leftJoin('lab_order_dtl as lod', 'loh.loh_labno',    '=', 'lod.lod_labno')
        ->leftJoin('test_codes   as ltc', 'lod.lod_testcode',  '=', 'ltc.tc_code')
        ->leftJoin('test_groups  as ltg', 'lod.lod_test_group','=', 'ltg.tg_code')
        ->leftJoin('trx_order_hdr as bth','loh.loh_trxno',     '=', 'bth.toh_trxno')
        ->leftJoin('genders       as g',  'g.code',            '=', 'bth.toh_gender')
        ->whereBetween('bth.toh_order_date', [$dateFrom, $dateTo])
        ->where('lod.lod_release_flag', 'Y')
        ->where('lod.lod_category', '<>', 'P')   // exclude parent panels
        ->whereNotNull('lod.lod_result');

    if ($tgCode !== '%') {
        $query->where('lod.lod_test_group', $tgCode);
    }

    $query->select([
        'loh.loh_labno        as lab_no',
        'bth.toh_pname        as patient_name',
        'g.description        as gender',
        'bth.toh_age_year     as age',
        'bth.toh_birthdate    as dob',
        'bth.toh_order_date   as order_date',
        'lod.lod_release_on   as released_dt',
        'lod.lod_release_by   as released_by',
        'ltg.tg_name          as test_group',
        'ltc.tc_short_name    as test_name',
        'ltc.tc_seqno         as test_seqno',
        'lod.lod_result       as result',
    ]);

    $query->orderBy('bth.toh_order_date')
          ->orderBy('loh.loh_labno')
          ->orderBy('ltc.tc_seqno');

    $flatRows = $query->get();

    // ------------------------------------------------------------
    // 2. Collect all distinct tests (columns) sorted by tc_seqno
    // ------------------------------------------------------------
    $testColumns = $flatRows
        ->sortBy('test_seqno')
        ->pluck('test_name')
        ->filter()
        ->unique()
        ->values()
        ->all();

    // ------------------------------------------------------------
    // 3. Pivot: group by lab_no → each test becomes a column
    // ------------------------------------------------------------
    $pivoted = $flatRows
        ->groupBy('lab_no')
        ->map(function ($rows, $labNo) use ($testColumns) {
            $first = $rows->first();

            $row = [
                'lab_no'       => $labNo,
                'patient_name' => $first->patient_name,
                'gender'       => $first->gender,
                'age'          => $first->age,
                'dob'          => $first->dob,
                'order_date'   => $first->order_date,
                'released_dt'  => $first->released_dt,
                'released_by'  => $first->released_by,
                'test_group'   => $first->test_group,
            ];

            // Initialize every test column to null
            foreach ($testColumns as $col) {
                $row[$col] = null;
            }

            // Fill values — if a lab has multiple results for the same test,
            // join them with " / " (rare case)
            foreach ($rows as $r) {
                if (!$r->test_name) continue;
                $row[$r->test_name] = $row[$r->test_name] === null
                    ? $r->result
                    : $row[$r->test_name] . ' / ' . $r->result;
            }

            return $row;
        })
        ->values();

    return response()->json([
        'filters' => [
            'date_from' => $dateFrom,
            'date_to'   => $dateTo,
            'tg_code'   => $tgCode,
        ],
        'columns' => array_merge(
            ['lab_no', 'patient_name', 'gender', 'age', 'dob', 'released_dt'],
            $testColumns
        ),
        'test_columns' => $testColumns,   // just the test names, for the table header
        'row_count'    => $pivoted->count(),
        'data'         => $pivoted,
        'generated_at' => now()->toDateTimeString(),
    ]);
}
}
