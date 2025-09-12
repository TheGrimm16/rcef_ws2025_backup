<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AggregateDashboardMetrics extends Command
{
    protected $signature = 'dashboard:aggregate-metrics';
    protected $description = 'Aggregate dashboard metrics and store in dashboard_metrics table';

    public function handle()
    {
        $this->info('Running aggregate metrics job...');
        // your logic here
        //$this->info('Season prefix: ' . (isset($GLOBALS['season_prefix']) ? $GLOBALS['season_prefix'] : '[not set]'));
        $today = Carbon::today()->toDateString();
        $now = Carbon::now();

        $table = config('app.season_prefix') . 'rcep_delivery_inspection.tbl_delivery';
        $this->info($table);
        $confirmed = DB::table($table)
            ->where('is_cancelled', 0)
            ->where('isBuffer', 0)
            ->select(DB::raw('SUM(totalBagCount) as total_bag_count'))
            ->first();

        DB::table('dashboard_metrics')->updateOrInsert(
            ['summary_date' => $today],
            [
                'total_bags' => isset($confirmed->total_bag_count) ? $confirmed->total_bag_count : 0,
                'total_equity' => 0,
                'total_cash' => 0,
                'total_trades' => 0,
                'total_deliveries' => 0,
                'total_cooperatives' => 0,
                'updated_at' => $now,
                'created_at' => $now
            ]
        );

        $this->info("Aggregated dashboard metrics for " . $today);
        \Log::info('Dashboard metrics updated', (array) $confirmed);//for debugging
    }
}
