<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TatReportController extends Controller
{
    public function index(Request $request)
    {
        // Force strings so dates/codes are safely quoted
        $dateFrom = (string) ($request->input('date_from') ?? now()->startOfDay()->toDateTimeString());
        $dateTo   = (string) ($request->input('date_to')   ?? now()->endOfDay()->toDateTimeString());
        $tgCode   = (string) ($request->input('tg_code')   ?? '%');
        $ptCode   = (string) ($request->input('pt_code')   ?? '%');
        $stId     = (string) ($request->input('st_id')     ?? '%');
        $userId   = (string) ($request->input('ua_user_id')?? '%');

        // ============================================================
        // Main TAT query — mirrors the VB.NET SQL from RP_TATRPT.vb
        // ============================================================
        $query = DB::table('trx_order_hdr as toh')
            ->leftJoin('trx_order_dtl  as tod', 'toh.toh_trxno',    '=', 'tod.tod_trxno')
            ->leftJoin('lab_order_dtl  as lod', 'lod.lod_labno',    '=', 'tod.tod_labno')
            ->leftJoin('lab_order_samples as los', function ($join) {
                $join->on('lod.lod_labno',       '=', 'los.los_labno')
                     ->on('lod.lod_sample_type', '=', 'los.los_sample_type');
            })
            ->leftJoin('patient_types as ptype', 'ptype.pt_code',   '=', 'toh.toh_ptype')
            ->leftJoin('source_table  as st',    'st.st_id',        '=', 'toh.toh_source')
            ->leftJoin('test_codes    as tc',    'tc.tc_code',      '=', 'lod.lod_order_testcode')
            ->whereNotNull('lod.lod_release_on')
            ->whereNotNull('los.los_received_on')
            ->whereBetween('toh.toh_order_date', [$dateFrom, $dateTo]);

        // Optional filters (same behavior as the VB.NET dropdowns)
        if ($tgCode !== '%') $query->where('lod.lod_test_group', $tgCode);
        if ($ptCode !== '%') $query->where('toh.toh_ptype',      $ptCode);
        if ($stId   !== '%') $query->where('toh.toh_source',     $stId);
        if ($userId !== '%') $query->where('lod.lod_release_by', $userId);

        $query = DB::table('trx_order_hdr as toh')
            ->leftJoin('trx_order_dtl  as tod', 'toh.toh_trxno', '=', 'tod.tod_trxno')
            ->leftJoin('lab_order_dtl  as lod', 'lod.lod_labno', '=', 'tod.tod_labno')
            ->leftJoin('lab_order_samples as los', function ($join) {
                $join->on('lod.lod_labno',       '=', 'los.los_labno')
                    ->on('lod.lod_sample_type', '=', 'los.los_sample_type');
            })
            ->leftJoin('patient_types as ptype', 'ptype.pt_code', '=', 'toh.toh_ptype')
            ->leftJoin('source_table  as st',    'st.st_id',     '=', 'toh.toh_source')
            ->leftJoin('test_codes    as tc',    'tc.tc_code',   '=', 'lod.lod_order_testcode')
            ->whereNotNull('lod.lod_release_on')
            ->whereNotNull('los.los_received_on')
            ->whereBetween('toh.toh_order_date', [$dateFrom, $dateTo]);

        if ($tgCode !== '%') $query->where('lod.lod_test_group', $tgCode);
        if ($ptCode !== '%') $query->where('toh.toh_ptype',      $ptCode);
        if ($stId   !== '%') $query->where('toh.toh_source',     $stId);
        if ($userId !== '%') $query->where('lod.lod_release_by', $userId);

        $query->groupBy([
                'toh.toh_trxno',              // ✅
                'toh.toh_pid',                // ✅
                'toh.toh_pname',              // ✅
                'toh.toh_order_date',         // ✅
                'ptype.pt_description',       // ✅
                'st.st_name',                 // ✅
                'los.los_specimen_no',        // ✅
                'lod.lod_order_testcode',     // ✅ ← THIS is the key one (BILIP, not TBIL/DBIL/IBIL)
                'lod.lod_test_group',         // ✅
                'lod.lod_priority',           // ✅
                'lod.lod_release_by',         // ✅
                'tc.tc_tat_routine',          // ✅
                'tc.tc_tat_stat',             // ✅
            ])
            ->select([
                'toh.toh_trxno           as trx_no',
                'toh.toh_pid             as patient_id',
                'toh.toh_pname           as patient_name',
                'toh.toh_order_date      as order_date',
                'ptype.pt_description    as patient_type',
                'st.st_name              as source',
                'los.los_specimen_no     as specimen_id',
                'lod.lod_order_testcode  as test_code',        // ✅ BILIP
                'lod.lod_test_group      as tg_code',
                'lod.lod_priority        as priority',
                'lod.lod_release_by      as release_by',
                'tc.tc_tat_routine       as target_routine_tat',
                'tc.tc_tat_stat          as target_stat_tat',
                DB::raw('MIN(los.los_received_on) AS checkin_date'),
                DB::raw('MIN(los.los_collected_on) AS collected_date'),
                DB::raw('MAX(lod.lod_release_on)  AS release_date'),
                DB::raw('TIMESTAMPDIFF(MINUTE, MIN(los.los_received_on), MAX(lod.lod_release_on)) AS tat_minutes'),
            ])
            ->orderBy('toh.toh_order_date', 'asc');

        $rows = $query->get();

        // ============================================================
        // Compute achieved / exceeded (mirrors VB.NET logic)
        // ============================================================
        $achieved = 0;
        $exceeded = 0;

        $data = $rows->map(function ($row) use (&$achieved, &$exceeded) {
            $tatHours = $row->tat_minutes ? $row->tat_minutes / 60 : null;

            $targetHours = $row->priority === 'R'
                ? (float) $row->target_routine_tat
                : (float) $row->target_stat_tat;

            $status = 'IN PROGRESS';
            if ($tatHours !== null) {
                if ($tatHours > $targetHours) {
                    $status = 'EXCEEDED';
                    $exceeded++;
                } else {
                    $status = 'WITHIN TAT';
                    $achieved++;
                }
            }

            return [
                'trx_no'             => $row->trx_no,
                'patient_id'         => $row->patient_id,
                'patient_name'       => $row->patient_name,
                'specimen_id'        => $row->specimen_id,
                'test_code'          => $row->test_code,
                'tg_code'            => $row->tg_code,
                'patient_type'       => $row->patient_type,
                'source'             => $row->source,
                'collected_date'     => $row->collected_date,
                'checkin_date'       => $row->checkin_date,   // 
                'release_date'       => $row->release_date,   // 
                'release_by'         => $row->release_by,
                'priority'           => $row->priority === 'R' ? 'Routine' : 'STAT',
                'target_hours'       => $targetHours,
                'actual_hours'       => $tatHours !== null ? round($tatHours, 2) : null,
                'tat_minutes'        => $row->tat_minutes,
                'tat_status'         => $status,
            ];
        });

        // ============================================================
        // Summary block (mirrors VB.NET header stats)
        // ============================================================
        $validTats = $rows->pluck('tat_minutes')->filter(fn($v) => !is_null($v));

        $summary = [
            'record_count'       => $rows->count(),
            'achieved_tat_count' => $achieved,
            'exceeded_tat_count' => $exceeded,
            'percent_achieved'   => $rows->count() > 0
                ? round(($achieved / $rows->count()) * 100, 1)
                : 0,
            'min_tat_hours'      => $validTats->count() ? round($validTats->min() / 60, 2) : null,
            'max_tat_hours'      => $validTats->count() ? round($validTats->max() / 60, 2) : null,
            'avg_tat_hours'      => $validTats->count() ? round($validTats->avg() / 60, 2) : null,
        ];

        return response()->json([
            'filters' => [
                'date_from'  => $dateFrom,
                'date_to'    => $dateTo,
                'tg_code'    => $tgCode,
                'pt_code'    => $ptCode,
                'st_id'      => $stId,
                'ua_user_id' => $userId,
            ],
            'summary'      => $summary,
            'data'         => $data,
            'generated_at' => now()->toDateTimeString(),
        ]);
    }

    public function filters()
    {
        return response()->json([
            'test_groups' => DB::table('test_groups')
                                ->where('tg_module', 'GEN')
                                ->orderBy('tg_name')
                                ->get(['tg_code', 'tg_name']),
            'patient_types' => DB::table('patient_types')
                                ->orderBy('pt_description')
                                ->get(['pt_code', 'pt_description']),
            'sources' => DB::table('source_table')
                                ->orderBy('st_name')
                                ->get(['st_id', 'st_name']),
            'users' => DB::table('user_accounts')
                                ->where('ua_active', 'Y')
                                ->orderBy('ua_user_name')
                                ->get(['ua_user_id', 'ua_user_name']),
        ]);
    }
}