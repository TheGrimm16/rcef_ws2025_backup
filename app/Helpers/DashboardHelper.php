<?php namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Providers;

class DashboardHelper
{
    protected static $instance;

    // Prevent duplication by checking if method already exists
    public static function __callStatic($method, $args)
    {
        if (method_exists(__CLASS__, $method)) {
            return call_user_func_array([new static, $method], $args);
        }

        throw new \BadMethodCallException("Method $method does not exist.");
    }

    public static function getAverageLandHolding()
    {
        $scriptPath = base_path('app/Http/PyScript/dashboard/average-landholding.py');
        $pythonPath = $GLOBALS['python_path'];
        $command = escapeshellcmd("$pythonPath \"$scriptPath\"");
        $output = [];
        $return_var = 0;
        exec($command, $output, $return_var);

        if ($return_var !== 0 || empty($output)) {
            \Log::error('Python script failed', ['return_var' => $return_var, 'output' => $output]);
            return 0;
        }

        $result = floatval(trim($output[0]));
        return $result;
    }

    public static function getRegions()
    {
        return DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
            ->where('region', '!=', '')
            ->groupBy('region')
            ->orderBy('region')
            ->get();
    }

    public static function getConfirmedBags()
    {
        return DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
            ->select(DB::raw('SUM(totalBagCount) as total_bag_count'))
            ->where('batchTicketNumber', 'NOT LIKE', '%void%')
            ->where('dropOffPoint', 'NOT LIKE', '%void%')
            ->where('is_cancelled', 0)
            ->first();
    }

    public static function getActualBags()
    {
        return DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
            ->select(DB::raw('SUM(totalBagCount) as total_bag_count'))
            ->where('batchTicketNumber', '!=', 'TRANSFER')
            ->first();
    }

    public static function getDistributed()
    {
        return DB::table($GLOBALS['season_prefix']."rcep_reports.lib_national_reports")
            ->first();
    }

    public static function getTarget($region, $province)
    {
        return DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery_sum")
            ->selectRaw('SUM(targetVolume) as total_target_volume')
            ->where('region', 'LIKE', $region)
            ->where('province', 'LIKE', $province)
            ->whereMonth('targetMonthFrom', 'LIKE', '2023-08-01')
            ->whereMonth('targetMonthTo', 'LIKE', '2024-02-29')
            ->first();
    }

    public static function getConfirmedDeliveryRegions($week)
    {
        $results = DB::select(DB::raw("SELECT deliveryId, tbl_delivery.region
            FROM {$GLOBALS['season_prefix']}rcep_delivery_inspection.tbl_delivery
            JOIN {$GLOBALS['season_prefix']}rcep_delivery_inspection.lib_prv 
                ON tbl_delivery.region = lib_prv.regionName
            WHERE tbl_delivery.region != '' 
                AND DATE(tbl_delivery.deliveryDate) BETWEEN :week_start AND :week_end
            GROUP BY tbl_delivery.region
            ORDER BY lib_prv.region_sort ASC"), [
            'week_start' => date("Y-m-d", strtotime($week['start'])),
            'week_end' => date("Y-m-d", strtotime($week['end'])),
        ]);

        return !empty($results) ? $results : "no_deliveries";
    }

    public static function getLatestMirrorDate()
    {
        $date = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_logs')
            ->where('category', 'DELIVERY_DATA_MIRROR')
            ->orderBy('id', 'DESC')
            ->value('date_recorded');

        return date("F j, Y g:i A", strtotime($date));
    }

    public static function getCoopInfo($userId)
    {
        $coop_accre = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.users_coop')
            ->where('userId', $userId)
            ->value('coopAccreditation');

        $coop_name = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')
            ->where('accreditation_no', $coop_accre)
            ->value('coopName');

        $regions = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')
            ->select('region_name')
            ->where('accreditation_no', $coop_accre)
            ->orderBy('region_name', 'asc')
            ->groupBy('region_name')
            ->get();

        return [
            'coop_accre' => $coop_accre,
            'coop_name' => $coop_name,
            'regions' => $regions,
            'regions_count' => count($regions)
        ];
    }
}

