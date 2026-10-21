<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ResultEntryController extends Controller
{
   public function labnoList(Request $request)
{
    $dateFrom   = $request->input('date_from', now()->toDateString());
    $dateTo     = $request->input('date_to',   now()->toDateString());
    $tg         = $request->input('tg_code',   '%');
    $releaseFlag= $request->input('release_flag', '%');
    $nameFilter = $request->input('name',      '%');
    $labnoFilter= $request->input('labno',     '%');
    $offset     = (int) $request->input('offset', 0);
    $limit      = 50;

    $q = DB::table('lab_order_hdr as loh')
        ->leftJoin('trx_order_hdr  as trx', 'loh.loh_trxno',  '=', 'trx.toh_trxno')
        ->leftJoin('lab_order_dtl  as lod', 'loh.loh_labno',  '=', 'lod.lod_labno')
        ->leftJoin('lab_order_samples as los', function ($j) {
            $j->on('lod.lod_labno',       '=', 'los.los_labno')
              ->on('lod.lod_sample_type', '=', 'los.los_sample_type');
        })
        ->leftJoin('test_groups    as tg',  'lod.lod_test_group', '=', 'tg.tg_code')
        ->where(function ($w) {
            $w->whereNull('tg.tg_module')->orWhere('tg.tg_module', 'GEN');
        });

    // Filters
    if ($labnoFilter !== '%') {
        $q->where(function ($w) use ($labnoFilter) {
            $w->whereRaw('UPPER(loh.loh_labno) LIKE ?', ["%{$labnoFilter}%"])
              ->orWhereRaw('UPPER(los.los_specimen_no) LIKE ?', ["%{$labnoFilter}%"]);
        });
    } else {
        $q->whereBetween(DB::raw('DATE(los.los_received_on)'), [$dateFrom, $dateTo]);
    }

    if ($nameFilter !== '%') {
        $q->where(function ($w) use ($nameFilter) {
            $w->whereRaw('UPPER(trx.toh_pid) LIKE ?',   ["%{$nameFilter}%"])
              ->orWhereRaw('UPPER(trx.toh_pname) LIKE ?', ["%{$nameFilter}%"]);
        });
    }

    if ($releaseFlag !== '%') $q->where('lod.lod_release_flag', $releaseFlag);
    if ($tg !== '%')          $q->where('lod.lod_test_group',  $tg);

    // Count
    $countQ = clone $q;
    $total  = $countQ->distinct()->count('loh.loh_labno');

    // Rows
    $rows = $q->distinct()
        ->select(
            'los.los_received_on  as sort_received',
            'loh.loh_labno as lab_no',
            'loh.loh_date  as lab_date',
            'trx.toh_pid   as patient_id',
            'trx.toh_pname as patient_name'
        )
        ->orderBy('los.los_received_on')
        ->offset($offset)->limit($limit)
        ->get();

    return response()->json([
        'total'       => $total,
        'offset'      => $offset,
        'limit'       => $limit,
        'rows'        => $rows,
    ]);
}
public function details(string $labno, Request $request)
{
    $tg = $request->input('tg_code', '%');

    // ---- Header ----
    $header = DB::table('lab_order_hdr as lab')
        ->leftJoin('trx_order_hdr as trx', 'lab.loh_trxno',      '=', 'trx.toh_trxno')
        ->leftJoin('pathologists  as ph',  'lab.loh_pathologist','=', 'ph.patho_id')
        ->leftJoin('genders       as g',   'trx.toh_gender',     '=', 'g.code')
        ->leftJoin('source_table  as sc',  'trx.toh_ward',       '=', 'sc.st_id')
        ->leftJoin('patient_types as pt',  'trx.toh_ptype',      '=', 'pt.pt_code')
        ->where('lab.loh_labno', $labno)
        ->select(
            'lab.loh_labno        as lab_no',
            'trx.toh_trxno        as trx_no',
            'trx.toh_his_orderno  as his_orderno',
            'trx.toh_pid          as patient_id',
            'trx.toh_pname        as patient_name',
            'lab.loh_date         as lab_date',
            'g.description        as gender',
            'trx.toh_birthdate    as birthdate',
            'trx.toh_age_year     as age_year',
            'trx.toh_age_month    as age_month',
            'trx.toh_age_day      as age_day',
            'pt.pt_description    as patient_type',
            'sc.st_name           as source',
            'trx.toh_roomno       as room_no',
            'lab.loh_global_comment as global_comment',
            'ph.patho_id          as pathologist_id',
            'ph.patho_name        as pathologist_name',
            'trx.toh_clinical_info as clinical_info'
        )
        ->first();

    // ---- Sample dates ----
    $sample = DB::table('lab_order_samples')
        ->where('los_labno', $labno)
        ->orderBy('los_received_on')
        ->select('los_collected_on', 'los_received_on', 'los_specimen_no')
        ->first();

    // ---- Results ----
    $resultsQ = DB::table('lab_order_dtl as dtl')
        ->leftJoin('lab_order_samples as samp', function ($j) {
            $j->on('dtl.lod_labno',       '=', 'samp.los_labno')
              ->on('dtl.lod_sample_type', '=', 'samp.los_sample_type');
        })
        ->leftJoin('test_codes as tc', 'dtl.lod_testcode',  '=', 'tc.tc_code')
        ->leftJoin('test_units as tu', 'dtl.lod_si_unit',   '=', 'tu.tu_code')
        ->where('dtl.lod_labno', $labno)
        ->where('dtl.lod_order_item', 'Y')
        ->where('dtl.lod_test_group', 'LIKE', $tg === '%' ? '%' : $tg)
        ->orderBy('dtl.lod_test_group')
        ->orderBy('dtl.lod_seqno')
        ->orderBy('dtl.lod_testcode')
        ->select(
            'dtl.lod_testcode                   as test_code',
            'dtl.lod_test_group                 as test_group',
            'dtl.lod_parent_testcode            as parent_test_code',
            'dtl.lod_order_testcode             as order_test_code',
            'dtl.lod_category                   as category',
            'dtl.lod_data_type                  as data_type',
            'dtl.lod_seqno                      as seq_no',
            'dtl.lod_result                     as result',
            'dtl.lod_result_flag                as result_flag',
            'dtl.lod_release_flag               as release_flag',
            'dtl.lod_rerun_flag                 as rerun_flag',
            'dtl.lod_edit_flag                  as edit_flag',
            'dtl.lod_prelim_release_flag        as prelim_release_flag',
            'dtl.lod_comment                    as comment',
            'dtl.lod_si_unit                    as si_unit_code',
            'dtl.lod_si_reference_description   as reference',
            'dtl.lod_normal_low_limit           as normal_low',
            'dtl.lod_normal_up_limit            as normal_up',
            'dtl.lod_panic_low_limit            as panic_low',
            'dtl.lod_panic_up_limit             as panic_up',
            'dtl.lod_1st_run_result             as run1',
            'dtl.lod_2nd_run_result             as run2',
            'dtl.lod_3rd_run_result             as run3',
            'tc.tc_name                         as test_name',
            'tc.tc_short_name                   as short_name',
            'tu.tu_description                  as unit',
            'samp.los_received_by               as received_by'
        );

    $rows = $resultsQ->get();

    // ---- Previous results (last result for same patient/test) ----
    $prevResults = DB::table('lab_order_dtl as lod')
        ->join('lab_order_hdr as loh', 'lod.lod_labno', '=', 'loh.loh_labno')
        ->join('trx_order_hdr as toh', 'toh.toh_trxno', '=', 'loh.loh_trxno')
        ->where('toh.toh_pid', $header->patient_id)
        ->where('loh.loh_labno', '<>', $labno)
        ->whereRaw('loh.loh_created_on < (SELECT loh_created_on FROM lab_order_hdr WHERE loh_labno = ?)', [$labno])
        ->whereIn('lod.lod_testcode', $rows->pluck('test_code')->unique())
        ->whereNotNull('lod.lod_result')
        ->where('lod.lod_result', '<>', '')
        ->select('lod.lod_testcode as test_code', 'lod.lod_result as result', 'loh.loh_date as date')
        ->orderBy('loh.loh_created_on', 'desc')
        ->get()
        ->groupBy('test_code')
        ->map(fn($g) => $g->first());  // latest per test

    // Attach to rows
    $rows = $rows->map(function ($r) use ($prevResults) {
        $prev = $prevResults->get($r->test_code);
        $r->prev_result    = $prev->result ?? null;
        $r->prev_result_dt = $prev->date   ?? null;
        return $r;
    });

    return response()->json([
        'header'  => $header,
        'sample'  => $sample,
        'results' => $rows,
    ]);
}
public function save(Request $request)
{
    $labno    = $request->input('labno');
    $trxno    = $request->input('trxno');
    $globalCmt= $request->input('global_comment');
    $pathoID  = $request->input('pathologist_id');
    $results  = $request->input('results', []);   // array of {test_code, result, result_flag, comment, ...}
    $userId   = auth()->user()->ua_user_id ?? 'SA';

    DB::beginTransaction();
    try {
        foreach ($results as $r) {
            DB::table('lab_order_dtl')
                ->where('lod_labno',    $labno)
                ->where('lod_testcode', $r['test_code'])
                ->update([
                    'lod_result'       => $r['result']       ?? '',
                    'lod_result_flag'  => $r['result_flag']  ?? 'N',
                    'lod_edit_flag'    => $r['edit_flag']    ?? 'N',
                    'lod_rerun_flag'   => $r['rerun_flag']   ?? 'N',
                    'lod_comment'      => $r['comment']      ?? '',
                    'lod_update_on'    => now(),
                    'lod_update_by'    => $userId,
                ]);

            // Event log
            DB::table('event_logs')->insert([
                'el_event'   => 'RESULT_SAVE',
                'el_trxno'   => $trxno,
                'el_labno'   => $labno,
                'el_user'    => $userId,
                'el_program' => 'RP_RESENT',
                'el_log_cmt' => "Test={$r['test_code']}; Result={$r['result']}",
                'el_date'    => now(),
            ]);
        }

        DB::table('lab_order_hdr')
            ->where('loh_labno', $labno)
            ->update([
                'loh_update_on'       => now(),
                'loh_update_by'       => $userId,
                'loh_global_comment'  => $globalCmt,
                'loh_pathologist'     => $pathoID,
            ]);

        DB::commit();
        return response()->json(['success' => true, 'message' => 'Saved.']);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}
public function filters()
{
    $userId = auth()->user()->ua_user_id ?? 'SA';

    $groups = DB::table('user_account_groups as uag')
        ->leftJoin('user_group_tg as ugt', 'uag.uag_user_group', '=', 'ugt.ugt_id')
        ->leftJoin('test_groups as tg',    'ugt.ugt_test_group', '=', 'tg.tg_code')
        ->where('uag.uag_user_id', $userId)
        ->whereNotNull('tg.tg_code')
        ->where(function ($q) {
            $q->whereNull('tg.tg_module')->orWhere('tg.tg_module', 'GEN');
        })
        ->distinct()
        ->orderBy('tg.tg_name')
        ->get(['tg.tg_code', 'tg.tg_name']);

    return response()->json([
        'test_groups' => $groups,
    ]);
}
public function history(string $labno)
{
    $rows = DB::table('event_logs')
        ->where('el_labno', $labno)
        ->orderBy('el_date', 'desc')
        ->get();

    return response()->json(['history' => $rows]);
}
}
