<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Auth;
use DB;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class SRADashboardController extends Controller
{
    public function index()
    {
        $total_count = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
            ->select('*')
            ->count();

        $total_beneficiaries = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
            ->select('*')
            ->count();

        $claimed = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
            ->select('tbl_beneficiaries.area', 'tbl_beneficiaries.bags', 'tbl_beneficiaries.sex')
            ->leftjoin($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries', 'tbl_beneficiaries.beneficiary_id', '=', 'tbl_claim.beneficiary_id')
            ->get();

        $percentage = number_format((($total_count / $total_beneficiaries) * 100), 2);

        $total_bags = 0;
        $total_area = 0;
        $male = 0;
        $female = 0;
        foreach ($claimed as $c) {
            $total_area = $total_area + floatval($c->area);
            $total_bags = $total_bags + floatval($c->bags);
            if (strtolower($c->sex) == "male") {
                $male++;
            } else if (strtolower($c->sex) == "female" || strtolower($c->sex) == "femal") {
                $female++;
            }
        }

        $total_area = number_format($total_area, 2);
        $total_bags = number_format($total_bags);
        $male = number_format($male);
        $female = number_format($female);
        $total_count = number_format($total_count);
        return view('sra_dashboard.dashboard', compact('total_count', 'total_area', 'total_bags', 'female', 'male', 'percentage'));
    }

    public function graphLoad(Request $request)
    {

        $delivery_arr = array();

        $delivery = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
            ->select("province", "prv")
            ->where("qrValStart", "!=", "")
            ->where("qrValEnd", "!=", "")
            ->groupBy("province")
            ->get();

        foreach ($delivery as $d) {
            $mun_data = [];
            $confirmed = 0;
            $delivered = 0;
            $claimed = 0;
            $confirm = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
                ->select('*')
                ->addSelect(DB::raw("SUM(tbl_actual_delivery.totalBagCount) as bags"))
                ->where("province", "=", $d->province)
                ->where("qrValStart", "!=", "")
                ->where("qrValEnd", "!=", "")
                ->groupBy('batchTicketNumber')
                ->get();

            foreach ($confirm as $c) {
                $delivery = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
                    ->select('*')
                    ->addSelect(DB::raw("SUM(totalBagCount) as bags"))
                    ->where("province", "=", $d->province)
                    ->where("batchTicketNumber", "=", $c->batchTicketNumber)
                    ->get();

                $delivered = $delivered + intval($delivery[0]->bags);
                $confirmed = $confirmed + intval($c->bags);
            }

            $claim = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")
                ->select('*')
                ->addSelect(DB::raw("SUM(bags) as bags"))
                ->leftjoin($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries', 'tbl_beneficiaries.beneficiary_id', '=', 'tbl_claim.beneficiary_id')
                ->where("tbl_claim.province", "=", $d->province)
                ->groupBy('tbl_beneficiaries.beneficiary_id')
                ->get();

            // if(count($confirm) > 0){
            //     $confirmed = $confirm[0]->bags;
            // }
            if (count($claim) > 0) {
                $claimed = $claim[0]->bags;
            }

            $muni = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
                ->select("province", "prv", "municipality")
                ->where("qrValStart", "!=", "")
                ->where("qrValEnd", "!=", "")
                ->where("province", $d->province)
                ->groupBy("province")
                ->groupBy("municipality")
                ->get();

            foreach ($muni as $m) {
                $m_confirmed = 0;
                $m_delivered = 0;
                $m_claimed = 0;
                $confirm_m = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
                    ->select('*')
                    ->addSelect(DB::raw("SUM(tbl_actual_delivery.totalBagCount) as bags"))
                    ->where("province", "=", $d->province)
                    ->where("municipality", "=", $m->municipality)
                    ->where("qrValStart", "!=", "")
                    ->where("qrValEnd", "!=", "")
                    ->groupBy('province')
                    ->groupBy('batchTicketNumber')
                    ->get();

                foreach ($confirm_m as $cm) {
                    $delivery_m = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
                        ->select('*')
                        ->addSelect(DB::raw("SUM(totalBagCount) as bags"))
                        ->where("province", "=", $d->province)
                        ->where("municipality", "=", $m->municipality)
                        ->where("batchTicketNumber", "=", $cm->batchTicketNumber)
                        ->first();

                    $m_delivered = $m_delivered + intval($delivery_m->bags);
                    $m_confirmed = $m_confirmed + intval($cm->bags);
                }
                $claim_m = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")
                    ->select('*')
                    ->addSelect(DB::raw("SUM(bags) as bags"))
                    ->leftjoin($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries', 'tbl_beneficiaries.beneficiary_id', '=', 'tbl_claim.beneficiary_id')
                    ->where("tbl_claim.province", "=", $d->province)
                    ->where("tbl_claim.municipality", "=", $m->municipality)
                    ->groupBy('tbl_beneficiaries.beneficiary_id')
                    ->get();

                // if(count($confirm_m) > 0){
                //     $m_confirmed = $confirm_m[0]->bags;
                // }
                if (count($claim_m) > 0) {
                    $m_claimed = $claim_m[0]->bags;
                }

                array_push($mun_data, array(
                    "municipality" => $m->municipality,
                    "confirmed" => intval($m_confirmed),
                    "delivered" => intval($m_delivered),
                    "claimed" => intval($m_claimed),
                ));
            }

            array_push($delivery_arr, array(
                "province" => $d->province,
                "confirmed" => intval($confirmed),
                "delivered" => intval($delivered),
                "claimed" => intval($claimed),
                "mun_data" => $mun_data,
            ));
        }

        return $delivery_arr;

    }

    public function troubleshooting()
    {
        $provinces = DB::table('lib_provinces')
            ->orderBy('provDesc', 'asc')
            ->pluck('provDesc', 'provCode');

        return view('sra_dashboard.troubleshooting', compact('provinces'));
    }

    public function troubleshooting_datatable(Request $request)
    {

        $query = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
            ->select('drop_off_point', 'preferred_variety', 'area', 'bags')
            ->addSelect(DB::raw("CONCAT(firstname,' ',middname,' ',lastname) as fullname"))
            ->where("is_active", "1");

        if ($request->province != "") {
            $provinces = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_provinces')
                ->select('*')
                ->where('provCode', $request->province)
                ->first();

            $query = $query->where("province", $provinces->provDesc);
        }

        if ($request->municipality != "") {
            $mun = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_municipalities')
                ->select('*')
                ->where('provCode', $request->province)
                ->where('citymunCode', $request->municipality)
                ->first();

            $query = $query->where("municipality", $mun->citymunDesc);
        }

        //   if($status == 'unverified'){
        //        $query = $query->where(function($q){
        //             $q->where('status', '0')
        //             ->orWhere('status', null);
        //        });
        //   }

        return Datatables::of($query)
            ->filterColumn('fullname', function ($query, $keyword) {
                $sql = "CONCAT(farmer_fname,' ',farmer_mname,' ',farmer_lname) like ?";
                $query->whereRaw($sql, ["%{$keyword}%"]);
            })
        //    ->addColumn('drop_off_point', function($data) {
        //     $dop = '<span style="width:100px;overflow-wrap: break-word;">'.$data->drop_off_point.'</span>';

        //     return $dop;
        // })
            ->make(true);
    }

    public function load_paymaya_baranggay(Request $request)
    {
        $provi = $request->input('province');
        $muni = $request->input('municipality');

        $provinces = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_provinces')
            ->select('*')
            ->where('provCode', $provi)
            ->first();
        $mun = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_municipalities')
            ->select('*')
            ->where('provCode', $provi)
            ->where('citymunCode', $muni)
            ->first();

        $barangay = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
            ->select('barangay')
            ->where('province', $provinces->provDesc)
            ->where('municipality', $mun->citymunDesc)
            ->groupBy('barangay')
            ->get();

        echo json_encode($barangay);
    }

    public function load_paymaya_varieties(Request $request)
    {
        $provi = $request->input('province');
        $muni = $request->input('municipality');

        $provinces = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_provinces')
            ->select('*')
            ->where('provCode', $provi)
            ->first();

        $varieties = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
            ->select('preferred_variety')
            ->where('province', $provinces->provDesc);

        if ($muni != "") {
            $mun = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_municipalities')
                ->select('*')
                ->where('provCode', $provi)
                ->where('citymunCode', $muni)
                ->first();
            $varieties = $varieties->where('municipality', $mun->citymunDesc);
        }

        $varieties = $varieties->groupBy('preferred_variety')
            ->get();

        echo json_encode($varieties);
    }

    // SRA Dashboard
    public function sra_dashboard()
    {
        $cur = $GLOBALS['season_prefix'];

        if(substr($cur,0,2)== "ds"){
            $season = "Dry Season";
        }else{
            $season = "Wet Season";
        }
        $season_year = substr($cur,2,4);
        $season_code = substr($GLOBALS['season_prefix'], 0, 6);

        $months = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.lib_season_weeks')
            ->select('*')
            ->where('season_code', 'LIKE', $season_code)
            ->groupBy('season_month')
            ->orderBy('sw_id')
            ->get();

    


        foreach ($months as $m) {
            $farmers_count = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                    $join->on('lib_prv.prv', '=', DB::raw("REPLACE(sed_verified.claiming_prv,'-','')"));
                })
                // ->where('lib_prv.isEbinhi', 1)
                ->where('sed_verified.status', 1)
                ->where('sowing_month', $m->season_month)
                ->count();

            $farmers_scheduled = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                    $join->on('lib_prv.prv', '=', DB::raw("REPLACE(sed_verified.claiming_prv,'-','')"));
                })
                // ->where('lib_prv.isEbinhi', 1)
                ->where('sed_verified.status', 1)
                ->where('sed_verified.isScheduled', 1)
                ->where('sowing_month', $m->season_month)
                ->count();

            $m->farmer_count = $farmers_count;
            $m->farmer_scheduled = $farmers_scheduled;
        }

        $total_farmers = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                $join->on('lib_prv.prv', '=', DB::raw("REPLACE(sed_verified.claiming_prv,'-','')"));
            })
            // ->where('lib_prv.isEbinhi', 1)
            ->where('sed_verified.status', 1)
            ->where('sowing_month', "!=", 0)
            ->count();
        
        
        return view('sra_dashboard.paymaya', compact('months', 'total_farmers'));
    }

    public function load_municipalities_list(Request $request)
    {

        // $season_year = \Config::get('constants.season_year');
        // $season_code = \Config::get('constants.season_code');
        // $season = \Config::get('constants.season');

        // if($season == "Dry Season"){
        //      $prev_season = (intval($season_year) - 1);
        //      $prev_season = 'DS'.$prev_season;
        // }else{
        //      $prev_season = (intval($season_year) - 1);
        //      $prev_season = 'WS'.$prev_season;
        // }

        $data = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->select(DB::raw("REPLACE(sed_verified.claiming_prv,'-','') as claiming_prv"),'sed_verified.muni_code', 'lib_prv.province as province_name', 'lib_prv.municipality as municipality_name')
            ->addSelect(DB::raw("COUNT(*) as farmer_count"))
            ->addSelect(DB::raw('COUNT(IF(isScheduled = 1,1,NULL)) farmer_scheduled'))
        //  ->leftjoin($GLOBALS['season_prefix'].'sdms_db_dev.lib_provinces', 'lib_provinces.provCode', '=', "sed_verified.prv_code")
        //  ->leftjoin($GLOBALS['season_prefix'].'sdms_db_dev.lib_municipalities', 'lib_municipalities.citymunCode', '=', "sed_verified.muni_code")
        //  ->leftjoin($GLOBALS['season_prefix'].'rcep_paymaya.lib_inspection_dates', 'lib_inspection_dates.prv', '=', "sed_verified.muni_code")
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                $join->on('lib_prv.prv', '=', DB::raw("REPLACE(sed_verified.claiming_prv,'-','')"));
            })
            // ->where('lib_prv.isEbinhi', 1)
            ->where('sowing_month', $request->month)
        
        //  ->where('sed_verified.prv_code', $request->prv_code)
        //  ->where('lib_inspection_dates.crop_season', $prev_season)
            ->groupBy('claiming_prv')
            ->orderBy('province')
            ->get();

        $month = $request->month;
        return view('sra_dashboard.include.municipalities', compact('data', 'month'));
    }

    public function load_baranggay_list(Request $request)
    {
        // dd($request->muni);
        $dop = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.lib_ebinhi_dop')
            ->where('prv_code', $request->muni)
            ->whereNull('isDeleted')
            ->first();

        if (count($dop) > 0) {
            $variety = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
                ->select('seedVariety')
                ->where('dropOffPoint', 'like', '%' . $dop->pickup_location . '%')
                ->where('is_cancelled', 0)
                ->groupBy('seedVariety')
                ->get();
            $varieties = [];
            foreach ($variety as $v) {
                $seedVariety = preg_replace("/(19|20)[0-9][0-9]/", '', $v->seedVariety);
                $seedVariety = preg_replace("/\s+/", ' ', $seedVariety);
                $varieties[] = ['seedVariety' => $seedVariety];
            }
            $variety = collect($varieties)
                ->groupBy('seedVariety');

        } else {
            $variety = [];
        }

        $dops = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.lib_ebinhi_dop')
            ->where('prv_code', $request->muni)
            ->whereNull('isDeleted')
            ->get();

        if (count($dops) == 0) {
            $dops = [];
        }

        $data = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->select('sed_verified.muni_code', 'sed_verified.province_name', 'sed_verified.municipality_name', 'lib_geocodes.name', 'sed_verified.barangay_code')
            ->addSelect(DB::raw("COUNT(*) as farmer_count"))
            ->addSelect(DB::raw('COUNT(IF(isScheduled = 1,1,NULL)) farmer_scheduled'))
            ->leftjoin($GLOBALS['season_prefix'].'sdms_db_dev.lib_geocodes', function ($join) {
                $join->on('lib_geocodes.geocode_brgy', '=', "sed_verified.barangay_code");
            })
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                $join->on('lib_prv.prv', '=', DB::raw("REPLACE(sed_verified.claiming_prv,'-','')"));
            })
            // ->where('lib_prv.isEbinhi', 1)
            ->where('sowing_month', $request->month)
            ->whereRaw("REPLACE(sed_verified.claiming_prv,'-','') =".$request->muni)
            ->groupBy('sed_verified.barangay_code')
            ->orderBy('province')
            ->get();

        $month = $request->month;
        $muni = $request->muni;
        $week_start = strtotime('today');
        $week_end = strtotime('today');

        $datedefault = date('m/d/Y H:i:s', $week_start) . " - " . date('m/d/Y H:i:s', $week_end);
        return view('sra_dashboard.include.barangay', compact('data', 'month', 'muni', 'variety', 'datedefault', 'dops'));
    }

    public function load_farmer_list(Request $request)
    {

        $data = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->select('*')
        //  ->addSelect(DB::raw("COUNT(*) as farmer_count"))
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_geocodes', function ($join) {
                $join->on('lib_geocodes.geocode_brgy', '=', "sed_verified.barangay_code");
            })
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                $join->on('lib_prv.prv', '=', DB::raw("REPLACE(sed_verified.claiming_prv,'-','')"));
            })
            // ->where('lib_prv.isEbinhi', 1)
            ->where('sowing_month', $request->month)
            ->whereRaw("REPLACE(sed_verified.claiming_prv,'-','') =".$request->muni)
            ->where('sed_verified.barangay_code', $request->brgy)
            ->where(function ($q) {
                $q->where('status', "=", 1);
            })
            ->get();

        $month = $request->month;
        $muni = $request->muni;
        $brgy = $request->brgy;
        return view('sra_dashboard.include.farmers', compact('data', 'month', 'muni', 'brgy'));
    }

    public function load_farmer_datatable(Request $request)
    {

        $data = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->select('*')
            ->addSelect(DB::raw("CONCAT(fname,' ',midname,' ',lname) as fullname"))
            ->addSelect(DB::raw("CONCAT(sowing_week,' of ',sowing_month,', ',sowing_year) as sowing"))
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_geocodes', function ($join) {
                $join->on('lib_geocodes.geocode_brgy', '=', "sed_verified.barangay_code");
            })
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                $join->on('lib_prv.prv', '=', DB::raw("REPLACE(sed_verified.claiming_prv,'-','')"));
            })
            // ->where('lib_prv.isEbinhi', 1)
            ->where('sowing_month', $request->month)
            ->whereRaw("REPLACE(sed_verified.claiming_prv,'-','') =".$request->muni)
            ->where('sed_verified.barangay_code', $request->brgy)
            ->where(function ($q) {
                $q->where('status', "=", 1);
                $q->orWhere('status', "=", 5);
            });

        return Datatables::of($data)
            ->filterColumn('fullname', function ($query, $keyword) {
                $sql = "CONCAT(fname,' ',midname,' ',lname) like ?";
                $query->whereRaw($sql, ["%{$keyword}%"]);
            })
            ->filterColumn('sowing', function ($query, $keyword) {
                $sql = "CONCAT(sowing_week,' of ',sowing_month,', ',sowing_year) like ?";
                $query->whereRaw($sql, ["%{$keyword}%"]);
            })
            ->addColumn('isScheduled', function ($data) {
                $button = "";
                if ($data->isScheduled == 1) {
                    $button = '<span class="label label-primary">scheduled</span>';
                }
                return $button;
            })
            ->make(true);
    }

    public function load_selected_farmer_datatable(Request $request)
    {

        $data = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->select('*')
            ->addSelect(DB::raw("CONCAT(fname,' ',midname,' ',lname) as fullname"))
            ->addSelect(DB::raw("CONCAT(sowing_week,' of ',sowing_month,', ',sowing_year) as sowing"))
            ->leftjoin($GLOBALS['season_prefix'].'sdms_db_dev.lib_geocodes', function ($join) {
                $join->on('lib_geocodes.geocode_brgy', '=', "sed_verified.barangay_code");
            })
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                $join->on('lib_prv.prv', '=', DB::raw("REPLACE(sed_verified.claiming_prv,'-','')"));
            })
            // ->where('lib_prv.isEbinhi', 1)
            ->where('isScheduled', 0)
            ->where('sowing_month', $request->month)
            ->whereRaw("REPLACE(sed_verified.claiming_prv,'-','') =".$request->muni);
          
        if (!is_array($request->brgy)) {
            $data = $data->limit(0);
        } else {
            $data = $data->where(function ($q) use ($request) {
                $q->whereIn('barangay_code', $request->brgy);
            });
            if (is_array($request->variety)) {
                $data = $data->where(function ($q) use ($request) {
                    $q->whereIn('preffered_variety1', $request->variety)
                        ->orWhereIn('preffered_variety2', $request->variety);
                });
            }

            // if($request->farmers_count == ""){
            //     $data = $data->limit(0);
            // }else{
            //     $data = $data->limit(intval($request->farmers_count));
            // }
        }





        return Datatables::of($data)
            ->filterColumn('fullname', function ($query, $keyword) {
                $sql = "CONCAT(fname,' ',midname,' ',lname) like ?";
                $query->whereRaw($sql, ["%{$keyword}%"]);
            })
            ->filterColumn('sowing', function ($query, $keyword) {
                $sql = "CONCAT(sowing_week,' of ',sowing_month,', ',sowing_year) like ?";
                $query->whereRaw($sql, ["%{$keyword}%"]);
            })
            ->addColumn('actions', function ($data) {
                if($data->status == '1'){
                    $button = '<button class="btn btn-button btn-warning btn-xs excludeFarmer" data-id="' . $data->sed_id . '" data-status="' . $data->status . '">exclude</button>';
                }else if($data->status == '5'){
                    $button = '<button class="btn btn-button btn-info btn-xs excludeFarmer" data-id="' . $data->sed_id . '" data-status="' . $data->status . '">include</button>';
                }
               
                return $button;
            })
            ->make(true);
    }

    public function exclude_farmer(Request $request)
    {
        $farmer = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->select('*')
            ->where('sed_id',  $request->id)
            ->where(function ($q) {
                $q->where('status', "=", 1);
                $q->orWhere('status', "=", 5);
            })
            ->first();
        $status = 5;
        $status_text = "excluded";
        if($farmer->status == 5){
            $status = 1;
            $status_text = "included";

        }

        DB::beginTransaction();
        try {
            $farmer_update = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->where('sed_id',  $request->id)
                ->where(function ($q) {
                    $q->where('status', "=", 1);
                    $q->orWhere('status', "=", 5);
                })
                ->update([
                    'status' => $status
                ]);

            DB::commit();
            return ['status' => 1, 'message' => 'farmer '. $status_text];
        } catch (\Exception $e) {
            DB::rollback();
            // Session::flash('error', 'Error adding user.');
            return ['status' => 0, 'message' => 'Error updating farmer.'];
        }

        return $request->id;
    }

    public function save_scheduling(Request $request)
    {

        if ($request->brgy == "") {
            return ['status' => 0, 'message' => '0 Farmers Selected'];
        }

        // if($request->variety == ""){
        //     return ['status' => 0, 'message' => '0 Farmers Selected'];
        // }
        $data = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->select('*')
            ->addSelect(DB::raw("CONCAT(fname,' ',midname,' ',lname) as fullname"))
            ->addSelect(DB::raw("CONCAT(sowing_week,' of ',sowing_month,', ',sowing_year) as sowing"))
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_geocodes', function ($join) {
                $join->on('lib_geocodes.geocode_brgy', '=', "sed_verified.barangay_code");
            })
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                $join->on('lib_prv.prv', '=', DB::raw("REPLACE(sed_verified.claiming_prv,'-','')"));
            })
            // ->where('lib_prv.isEbinhi', 1)
            ->where('isScheduled', 0)
            ->where('sowing_month', $request->month)
            ->whereRaw("REPLACE(sed_verified.claiming_prv,'-','') =".$request->muni)
            ->where(function ($q) {
                $q->where('status', "=", 1);
            });

        if (!is_array($request->brgy)) {
            $data = $data->limit(0);
        } else {
            $data = $data->where(function ($q) use ($request) {
                $q->whereIn('barangay_code', $request->brgy);
            });
            if (is_array($request->variety)) {
                $data = $data->where(function ($q) use ($request) {
                    $q->whereIn('preffered_variety1', $request->variety)
                        ->orWhereIn('preffered_variety2', $request->variety);
                });
            }

            // if($request->farmers_count == ""){
            //     $data = $data->limit(0);
            // }else{
            //     $data = $data->limit(intval($request->farmers_count));
            // }
        }

        if ($request->farmerLimit != "") {
            $data = $data->limit($request->farmerLimit);
        }

        $data = $data->get();

        $DOP = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.lib_ebinhi_dop')
            ->where('ebinhi_dopID', $request->dop)
            ->where('prv_code', $request->muni)
            ->whereNull('isDeleted')
            ->first();

        $date = explode(" - ", $request->daterange);

        if (count($data) == 0) {
            return ['status' => 0, 'message' => '0 Farmers Selected'];
        }
        DB::beginTransaction();
        try {

            $date = explode(" - ", $request->daterange);
            $batch_id = 'EBC-' . $this->getToken(7);
            $first_token = 'B' . $this->getToken(2);

            $date1 = strtotime($date[0]);
            $date[0] = date('Y-m-d', $date1);
            $date2 = strtotime($date[1]);
            $date[1] = date('Y-m-d', $date2);

            $batch = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_scheduler')
                ->insertGetId([
                    'sched_batch' => $batch_id,
                    'sched_time' => $request->schedtime,
                    'date_from' => $date[0],
                    'date_to' => $date[1],
                    'municode' => $request->muni,
                    'created_by' => Auth::user()->userId,
                ]);

            $coop_accred = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')
                ->where('coopName', 'LIKE', '%' . $DOP->coop_name . '%')
                ->where('isActive', 1)
                ->first();
            // dd($coop_accred);
            if (count($coop_accred) == 0) {
                return ['status' => 0, 'message' => 'No coop name found in DOP'];
            }
            foreach ($data as $f) {

                // $icts_area = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.kim_final_list')
                //     ->where('rsbsa', $f->rsbsa_control_number)
                //     ->first();
                $ict_area = [];
                if (count($ict_area) > 0) {
                    $bags = $icts_area->bags;
                    $area = $icts_area->area;
                } else {
                    // if($f->farm_area_ws2021 != 0){
                    //     if($f->committed_area <= $f->farm_area_ws2021){
                    //         $area = $f->committed_area;
                    //     }else{
                    //         $area = $f->farm_area_ws2021;
                    //     }
                    // }else if($f->farm_area_ds2021 != 0){
                    //     if($f->committed_area <= $f->farm_area_ds2021){
                    //         $area = $f->committed_area;
                    //     }else{
                    //         $area = $f->farm_area_ds2021;
                    //     }
                    // }else{
                    //     $area = $f->committed_area;
                    // }

                    if ($f->farm_area_ds2021 != 0) {
                        if ($f->committed_area <= $f->farm_area_ds2021) {
                            $area = $f->committed_area;
                        } else {
                            $area = $f->farm_area_ds2021;
                        }
                    } else if ($f->farm_area_ws2021 != 0) {
                        if ($f->committed_area <= $f->farm_area_ws2021) {
                            $area = $f->committed_area;
                        } else {
                            $area = $f->farm_area_ws2021;
                        }
                    } else {
                        $area = $f->committed_area;
                    }

                    $whole = floor($area);
                    $fraction = $area - $whole;
                    $bags = intval($area) * 2;

                    if ($fraction <= 0.5 && $fraction != 0.0) {
                        $bags = $bags + 1;
                    } else if ($fraction > 0.5 && $fraction != 0.0) {
                        $bags = $bags + 2;
                    }
                }

                $paymaya_code = $first_token . $this->getToken(4);
                $check_code = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
                    ->where('paymaya_code', $paymaya_code)
                    ->count();

                while ($check_code > 0) {
                    $paymaya_code = $first_token . $this->getToken(4);
                    $check_code = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
                        ->where('paymaya_code', $paymaya_code)
                        ->count();
                }
                $contactno = $f->secondary_contact_no;
                if ($f->secondary_contact_no == null || $f->secondary_contact_no == "") {
                    $contactno = $f->contact_no;
                    if (strlen($f->contact_no) == 10) {
                        $contactno = "0" . $f->contact_no;
                    }
                }

                switch ($f->sowing_week) {
                    case '1st Week':
                        $sowing_week = '01';
                        break;
                    case '2nd Week':
                        $sowing_week = '02';
                        break;
                    case '3rd Week':
                        $sowing_week = '03';
                        break;
                    case '4th Week':
                        $sowing_week = '04';
                        break;
                    case '5th Week':
                        $sowing_week = '05';
                        break;
                    default:
                        $sowing_week = null;
                        break;
                }
                $sowing_month = new \DateTime($f->sowing_month);
                // $sowing_date = $f->sowing_year.'/'. $sowing_month->format('m') .'/'. $sowing_week;
                $sowing_date = $sowing_month->format('m') .'/'. $sowing_week;
                $farmer = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
                    ->insert([
                        // 'paymaya_code' => $paymaya_code,
                        'paymaya_code' => $f->rcef_id,
                        'batch_code' => $batch_id,
                        'contact_no' => $contactno,
                        'province' => $DOP->province,
                        'municipality' => $DOP->municipality,
                        'drop_off_point' => $DOP->pickup_location,
                        'schedule_start' => $date[0],
                        'schedule_end' => $date[1],
                        'rsbsa_control_no' => $f->rsbsa_control_number,
                        'firstname' => $f->fname,
                        'middname' => $f->midname,
                        'lastname' => $f->lname,
                        'extname' => $f->extename,
                        'area' => $area,
                        'bags' => $bags,
                        'region' => $f->regionName,
                        'province2' => $f->province_name,
                        'municipality2' => $f->municipality_name,
                        'barangay' => $f->name,
                        'is_active' => 1,
                        'sex' => $f->ver_sex,
                        'coop_accreditation' => $coop_accred->accreditation_no,
                        'sed_id_fk' => $f->sed_id,
                        'prev_total_production' => $f->yield_no_bags,
                        'prev_ave_wt_bag' => $f->yield_weight_bags,
                        'prev_area_harvested' => $f->yield_area,
                        'prev_yield' => $f->yield,
                        'sowing_date' => $sowing_date,
                        'crop_establishment' => $f->crop_establishment,
                        'eco_system' => $f->ecosystem,
                    ]);

                if ($farmer) {
                    $variety = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_paymaya_lib')
                        ->insert([
                            // 'paymaya_code' => $paymaya_code,
                            'paymaya_code' => $f->rcef_id,
                            'date_created' => $batch_id,
                            'variety_1' => $f->preffered_variety1,
                            'variety_2' => $f->preffered_variety2,
                        ]);

                    $update = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                        ->where('sed_id', $f->sed_id)
                        ->update([
                            'isScheduled' => 1,
                        ]);
                    $prvdb = 'prv_'.$f->prv_code;
                    $farmer_table = $GLOBALS['season_prefix']. $prvdb . ".farmer_information_final";
                    $tag = DB::table($farmer_table)
                        ->where('rcef_id', 'like', '%' . $f->rcef_id . '%')
                        ->update([
                            'is_ebinhi' => 1,
                        ]);
                }

            }
            DB::commit();
            return ['status' => 1, 'message' => 'Added e-binhi schedule successfully.'];
        } catch (\Exception $e) {
            DB::rollback();
            // Session::flash('error', 'Error adding user.');
            return ['status' => 0, 'message' => 'Error Adding e-binhi Schedule', 'error' => $e];
        }

    }

    // To generate prv codes
    public function extract_baranggay_code()
    {
        $data = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->select('sed_id', 'rsbsa_control_number')
            ->whereNull('barangay_code')
            ->limit(10000)
            ->get();

        foreach ($data as $d) {
            $codes = explode("-", $d->rsbsa_control_number);
            $barangay_codes = $codes[0] . $codes[1] . $codes[2] . $codes[3];
            $update = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->where('sed_id', $d->sed_id)
                ->update([
                    'barangay_code' => $barangay_codes,
                ]);
        }
    }

    // drop off points
    public function dop_view()
    {
        $coops = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')
            ->where('isActive', 1)
            ->orderBy('coopName', 'asc')
            ->groupBy('coopName')
            ->get();

        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            // ->where('isEbinhi', 1)
            ->orderBy('province', 'asc')
            ->groupBy('province')
            ->get();

        return view('sra_dashboard.dop', compact('provinces', 'coops'));
    }

    public function load_dop_datatable()
    {
        $data = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.lib_ebinhi_dop')
            ->whereNull('isDeleted');

        return Datatables::of($data)
            ->addColumn('actions', function ($data) {
                $button = '<button class="btn btn-button btn-info btn-xs editDOP" data-id="' . $data->ebinhi_dopID . '">EDIT DOP</button>';
                $button .= '<button class="btn btn-button btn-primary btn-xs viewDOPFarmers" data-id="' . $data->ebinhi_dopID . '">Edited Scheduled Farmers</button>';
                // if($data->isDeleted == null){
                //     $button .= '<button class="btn btn-button btn-danger btn-xs deleteDOP">DELETE DOP</button>';
                // }else{
                //     $button .= '<button class="btn btn-button btn-success btn-xs recoverDOP">RRECOVER DOP</button>';
                // }
                return $button;
            })
            ->make(true);
    }

    public function edit_view_dop(Request $request)
    {
        $data = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.lib_ebinhi_dop')
            ->where('ebinhi_dopID', $request->id)
            ->whereNull('isDeleted')
            ->first();

        return view('sra_dashboard.include.edit_dop', compact('data'));
    }

    public function edit_form_dop(Request $request)
    {
        $data = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.lib_ebinhi_dop')
            ->where('ebinhi_dopID', $request->id)
            ->whereNull('isDeleted')
            ->first();

        DB::beginTransaction();
        try {
            $dop = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.lib_ebinhi_dop')
                ->where('ebinhi_dopID', 'like', $data->ebinhi_dopID)
                ->update([
                    'pickup_location' => $request->dop,
                    'old_pickup_location' => $data->pickup_location,
                ]);

            // if($tag){
            //     $tag = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            //     ->where('sed_id', $d->sed_id)
            //     ->update([
            //         'isTag' => 1,
            //     ]);
            // }

            DB::commit();
            return ['status' => 1, 'message' => 'Updated Drop Off Point successfully.'];
        } catch (\Exception $e) {
            DB::rollback();
            // Session::flash('error', 'Error adding user.');
            return ['status' => 0, 'message' => 'Error updating Drop Off Point.'];
        }
    }

    public function edit_form_dop_farmers(Request $request)
    {
        $data = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.lib_ebinhi_dop')
            ->where('ebinhi_dopID', $request->id)
            ->whereNull('isDeleted')
            ->first();

        $farmers = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
            ->where('drop_off_point', $data->pickup_location)
            ->get();
        // dd($farmers);
        DB::beginTransaction();
        try {
            $dop = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.lib_ebinhi_dop')
                ->where('ebinhi_dopID', 'like', $data->ebinhi_dopID)
                ->update([
                    'pickup_location' => $request->dop,
                    'old_pickup_location' => $data->pickup_location,
                ]);

            if ($dop) {
                foreach ($farmers as $f) {
                    $tag = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
                        ->where('beneficiary_id', $f->beneficiary_id)
                        ->update([
                            'drop_off_point' => $request->dop,
                            'isSendDOP' => 1,
                        ]);
                }

            }

            DB::commit();
            return ['status' => 1, 'message' => 'Updated Drop Off Point successfully.'];
        } catch (\Exception $e) {
            DB::rollback();
            // Session::flash('error', 'Error adding user.');
            return ['status' => 0, 'message' => 'Error updating Drop Off Point.'];
        }
    }

    public function view_scheduled_farmers(Request $request)
    {
        $data = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.lib_ebinhi_dop')
            ->where('ebinhi_dopID', $request->id)
            ->whereNull('isDeleted')
            ->first();

        return view('sra_dashboard.include.view_edited_farmers', compact('data'));
    }

    public function view_scheduled_farmers_datatable(Request $request)
    {
        $dop = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.lib_ebinhi_dop')
            ->where('ebinhi_dopID', $request->id)
            ->whereNull('isDeleted')
            ->first();

        $data = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
            ->select('*')
            ->addSelect(DB::raw("CONCAT(firstname,' ',middname,' ',lastname) as fullname"))
            ->where('is_waived', 0)
            ->where('isSendDOP', 1)
            ->where('drop_off_point', $dop->pickup_location);

        return Datatables::of($data)
            ->filterColumn('fullname', function ($query, $keyword) {
                $sql = "CONCAT(firstname,' ',middname,' ',lastname) like ?";
                $query->whereRaw($sql, ["%{$keyword}%"]);
            })
        // ->addColumn('isSent', function($data) {
        //     $button = "";
        //     if($data->isSendDOP == 1){
        //         $button = '<span class="label label-primary">Sent</span>';
        //     }
        //     return $button;
        // })
            ->make(true);
    }

    public function getFarmersDOPDetails(Request $request)
    {
        $dop = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.lib_ebinhi_dop')
            ->where('ebinhi_dopID', $request->id)
            ->whereNull('isDeleted')
            ->first();

        $data = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
            ->select('*')
            ->addSelect(DB::raw("CONCAT(firstname,' ',middname,' ',lastname) as fullname"))
            ->addSelect(DB::raw("CONCAT(firstname,' ') as date"))
            ->addSelect(DB::raw("CONCAT(firstname, ' ') as time"))
            ->leftjoin($GLOBALS['season_prefix'].'rcep_paymaya.tbl_scheduler', function ($join) {
                $join->on('tbl_scheduler.sched_batch', '=', "tbl_beneficiaries.batch_code");
            })
            ->where('is_waived', 0)
            ->where('isSendDOP', 1)
            ->where('drop_off_point', $dop->pickup_location)
            ->get();

        // $return['data'] = $data;
        // $return['date'] = "";
        // $return['time'] = "";

        if (count($data) > 0) {
            foreach ($data as $key => $d) {
                //   dd($key);
                $f = strtotime($d->date_from);
                $fromm = date("F", $f);
                $fromd = date("j", $f);
                $fromy = date("Y", $f);

                $t = strtotime($d->date_to);
                $tom = date("F", $t);
                $tod = date("j", $t);

                if ($fromm != $tom) {
                    $date = $fromm . " " . $fromd . "-" . $tom . " " . $tod . ", " . $fromy;
                } else if ($fromd != $tod) {
                    $date = $fromm . " " . $fromd . "-" . $tod . ", " . $fromy;
                } else {
                    $date = $fromm . " " . $fromd . ", " . $fromy;
                }
                // $return['date'] = $date;
                if ($d->sched_time == "AM") {
                    $time = "8:00 AM-12:00 PM";
                } else {
                    $time = "1:00 PM-4:00 PM";
                }

                $return[] = (object) [
                    'date' => $date,
                    'time' => $time,
                    'firstname' => $d->firstname,
                    'lastname' => $d->lastname,
                    'drop_off_point' => $d->drop_off_point,
                    'paymaya_code' => $d->paymaya_code,
                    'bags' => $d->bags,
                    'beneficiary_id' => $d->beneficiary_id,
                    'contact_no' => $d->contact_no,
                ];
            }

        }

        return $return;
    }

    public function update_sent_dop_status(Request $request)
    {
        $update = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
            ->where('beneficiary_id', $request->beneficiary_id)
            ->update([
                'isSendDOP' => $request->isSent,
            ]);

        return $update;
    }

    public function municipality(Request $request)
    {

        $provCode = $request->input('province');

        $municipalities = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            // ->where('isEbinhi', 1)
            ->where('province', $provCode)
            ->orderBy('municipality', 'asc')
            ->groupBy('municipality')
            ->get();

        echo json_encode($municipalities);
    }

    public function save_dop(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'province' => 'required',
            'municipality' => 'required',
            'coop_name' => 'required',
            'pickup_location' => 'required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            return ['status' => 0, 'message' => $errors->all()];
        }

        $input = $request->all();

        DB::beginTransaction();
        try {
            $userId = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.lib_ebinhi_dop')
                ->insertGetId([
                    'province' => $request->province,
                    'municipality' => $request->municipality,
                    'coop_name' => $request->coop_name,
                    'pickup_location' => $request->pickup_location,
                    'prv_code' => $request->prv_code,
                ]);

            DB::commit();
            return ['status' => 1, 'message' => 'Added DOP successfully.'];
        } catch (\Exception $e) {
            DB::rollback();
            // Session::flash('error', 'Error adding user.');
            return ['status' => 0, 'message' => 'Error adding DOP.'];
        }

    }

    public function crypto_rand_secure($min, $max)
    {
        $range = $max - $min;
        if ($range < 1) {
            return $min;
        }
        // not so random...
        $log = ceil(log($range, 2));
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd > $range);
        return $min + $rnd;
    }

    public function getToken($length)
    {
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        // $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet .= "0123456789";
        $max = strlen($codeAlphabet); // edited

        for ($i = 0; $i < $length; $i++) {
            $token .= $codeAlphabet[$this->crypto_rand_secure(0, $max - 1)];
        }

        return $token;
    }

    // utility page
    public function utility()
    {
        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->select('sed_verified.prv_code', 'sed_verified.province_name', 'sed_verified.municipality_name')
            ->addSelect(DB::raw("COUNT(*) as farmer_count"))
            ->addSelect(DB::raw('COUNT(IF(isScheduled = 1,1,NULL)) farmer_scheduled'))
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                $join->on('lib_prv.prv', '=', DB::raw("REPLACE(sed_verified.claiming_prv,'-','')"));
            })
            // ->where('lib_prv.isEbinhi', 1)
            ->where(function ($q) {
                $q->where('status', "=", 1);
            })
            ->groupBy('sed_verified.prv_code')
            ->orderBy('province')
            ->get();

        return view('sra_dashboard.utility', compact('provinces'));

    }

    public function getUntaggedCount(Request $request)
    {
        $table = $GLOBALS['season_prefix']."prv_" . $request->municode . ".farmer_profile_processed";
        $data = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->select('rsbsa_control_number', 'sed_verified.prv_code', 'sed_verified.province_name', 'sed_verified.municipality_name')
            ->addSelect(DB::raw("COUNT(*) as farmer_count"))
        //  ->join($table, function($join){
        //     $join->on('farmer_profile_processed.rsbsa_control_no', '=', "sed_verified.rsbsa_control_number");
        //  })
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                $join->on('lib_prv.prv', '=', DB::raw("REPLACE(sed_verified.claiming_prv,'-','')"));
            })
            // ->where('lib_prv.isEbinhi', 1)
            ->where('sed_verified.prv_code', $request->municode)
            ->where('sed_verified.isTag', 0)
            ->where(function ($q) {
                $q->where('status', "=", 1);
            })
            ->groupBy('sed_id')
            ->get();

        $province['province_name'] = "NULL";
        $province['farmer_count'] = 0;
        $province['prvcode'] = 0;
        if (count($data) > 0) {
            $province['province_name'] = $data[0]->province_name;
            $province['farmer_count'] = count($data);
            $province['prvcode'] = $data[0]->prv_code;
        }

        return $province;
    }

    public function tagUntagged(Request $request)
    {
        $table = $GLOBALS['season_prefix']."prv_" . $request->municode . ".farmer_profile_processed";
        $data = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->select('sed_id', 'rsbsa_control_number', 'sed_verified.prv_code', 'sed_verified.province_name', 'sed_verified.municipality_name')
            ->addSelect(DB::raw("COUNT(*) as farmer_count"))
        //  ->join($table, function($join){
        //     $join->on('farmer_profile_processed.rsbsa_control_no', '=', "sed_verified.rsbsa_control_number");
        //  })
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                $join->on('lib_prv.prv', '=', DB::raw("REPLACE(sed_verified.claiming_prv,'-','')"));
            })
            // ->where('lib_prv.isEbinhi', 1)
            ->where('sed_verified.prv_code', $request->municode)
            ->where('sed_verified.isTag', 0)
            ->where(function ($q) {
                $q->where('status', "=", 1);
            })
            ->groupBy('sed_id')
            ->get();

        DB::beginTransaction();
        try {
            foreach ($data as $d) {
                $tag = DB::table($table)
                    ->where('rsbsa_control_no', 'like', '%' . $d->rsbsa_control_number . '%')
                    ->update([
                        'is_ebinhi' => 1,
                    ]);

                if ($tag) {
                    $tag = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                        ->where('sed_id', $d->sed_id)
                        ->update([
                            'isTag' => 1,
                        ]);
                }

            }
            DB::commit();
            return ['status' => 1, 'message' => 'Updated Tag successfully.'];
        } catch (\Exception $e) {
            DB::rollback();
            // Session::flash('error', 'Error adding user.');
            return ['status' => 0, 'message' => 'Error updating Tag.'];
        }
    }

    // for scheduled farmers
    public function scheduled_farmers()
    {
        $data = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->select('sed_verified.muni_code', 'sed_verified.province_name', 'sed_verified.municipality_name')
            ->addSelect(DB::raw("COUNT(*) as farmer_count"))
            ->addSelect(DB::raw('COUNT(IF(isScheduled = 1,1,NULL)) farmer_scheduled'))
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                $join->on('lib_prv.prv', '=', DB::raw("REPLACE(sed_verified.claiming_prv,'-','')"));
            })
            // ->where('lib_prv.isEbinhi', 1)
        //  ->where('isScheduled', 1)
            ->where(function ($q) {
                $q->where('status', "=", 1);
            })
            ->groupBy('sed_verified.claiming_prv')
            ->orderBy('province')
            ->get();

        return view('sra_dashboard.scheduled_farmers', compact('data'));
    }

    public function load_scheduled_batch(Request $request)
    {
        $data = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
            ->select('*')
            ->addSelect(DB::raw("COUNT(*) as farmer_count"))
            ->addSelect(DB::raw("COUNT(IF(isSent = 1,1,NULL)) sent_count"))
            ->leftjoin($GLOBALS['season_prefix'].'rcep_paymaya.tbl_scheduler', function ($join) {
                $join->on('tbl_scheduler.sched_batch', '=', "tbl_beneficiaries.batch_code");
            })
            ->where('municode', $request->muni)
            ->whereNull('isDeleted')
            ->groupBy('batch_code')
            ->get();

        $muni = $request->muni;
        return view('sra_dashboard.include.scheduled_batch', compact('data', 'muni'));
    }

    public function load_scheduled_farmer_list(Request $request)
    {

        $muni = $request->muni;
        $batch = $request->batch_code;
        return view('sra_dashboard.include.scheduled_farmers', compact('muni', 'batch'));
    }

    public function load_scheduled_farmer_datatable(Request $request)
    {

        $data = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
            ->select('*')
            ->addSelect(DB::raw("CONCAT(firstname,' ',middname,' ',lastname) as fullname"))
            ->where('is_waived', 0)
            ->where('batch_code', $request->batch);

        return Datatables::of($data)
            ->filterColumn('fullname', function ($query, $keyword) {
                $sql = "CONCAT(firstname,' ',middname,' ',lastname) like ?";
                $query->whereRaw($sql, ["%{$keyword}%"]);
            })
            ->addColumn('isSent', function ($data) {
                $button = "";
                if ($data->isSent == 1) {
                    $button = '<span class="label label-primary">Sent</span>';
                }
                return $button;
            })
            ->make(true);
    }

    public function getBatchDetails(Request $request)
    {
        $data = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
            ->select('*')
            ->addSelect(DB::raw("CONCAT(firstname,' ',middname,' ',lastname) as fullname"))
            ->where('is_waived', 0)
            ->where('isSent', 0)
            ->where('batch_code', $request->batch)
            ->get();

        $return['data'] = $data;
        $return['date'] = "";
        $return['time'] = "";

        if (count($data) > 0) {
            $d = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_scheduler')
                ->where('sched_batch', $request->batch)
                ->first();

            $f = strtotime($d->date_from);
            $fromm = date("F", $f);
            $fromd = date("j", $f);
            $fromy = date("Y", $f);

            $t = strtotime($d->date_to);
            $tom = date("F", $t);
            $tod = date("j", $t);

            if ($fromm != $tom) {
                $date = $fromm . " " . $fromd . "-" . $tom . " " . $tod . ", " . $fromy;
            } else if ($fromd != $tod) {
                $date = $fromm . " " . $fromd . "-" . $tod . ", " . $fromy;
            } else {
                $date = $fromm . " " . $fromd . ", " . $fromy;
            }

            // if($fromm != $tom){
            //     $date = $fromm." ".$fromd." at ".$tom." ".$tod.", ".$fromy;
            // }else if($fromd != $tod){
            //     $date = $fromm." ".$fromd." at ".$tod.", ".$fromy;
            // }else{
            //     $date = $fromm." ".$fromd.", ".$fromy;
            // }

            $return['date'] = $date;
            // if($d->am_pref != null && $d->pm_pref != null){
            //     if($d->sched_time == "AM"){
            //         $return['time'] =  $d->am_pref;
            //     }else{
            //         $return['time'] = $d->pm_pref;
            //     }
            // }else{
            //     if($d->sched_time == "AM"){
            //         $return['time'] = "8:00 AM-12:00 PM";
            //     }else{
            //         $return['time'] = "1:00 PM-4:00 PM";
            //     }
            // }
            // if ($d->municode == "035411") {
            //     if ($d->sched_time == "AM") {
            //         $return['time'] = "9:00 AM-12:00 PM";
            //     } else {
            //         $return['time'] = "1:00 PM-3:00 PM";
            //     }
            // } else if ($d->municode == "031421") {
            //     if ($d->sched_time == "AM") {
            //         $return['time'] = "7:30 AM-11:30 PM";
            //     } else {
            //         $return['time'] = "1:00 PM-3:30 PM";
            //     }
            // } else if ($d->municode == "031422") {
            //     if ($d->sched_time == "AM") {
            //         $return['time'] = "7:30 AM-11:30 PM";
            //     } else {
            //         $return['time'] = "1:00 PM-3:30 PM";
            //     }
            // } else {
            //     if ($d->sched_time == "AM") {
            //         $return['time'] = "8:00 AM-12:00 PM";
            //     } else {
            //         $return['time'] = "1:00 PM-4:00 PM";
            //     }
            // }

            $dis_time = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_distribution_time')
                ->select('*')
                ->where('muni_code', $d->municode)
                ->first();
            
            if($dis_time != null){
                if ($d->sched_time == "AM") {
                    $pmoram = "AM";
                    if($dis_time->am_end == "12:00" || $dis_time->am_end == "12:30"){
                        $pmoram = "PM";
                    }
                    $return['time'] = $dis_time->am_start." AM-".$dis_time->am_end	." ".$pmoram;
                } else {
                    $return['time'] = $dis_time->pm_start." PM-".$dis_time->pm_end	." PM";
                }
            }else{
                if ($d->sched_time == "AM") {
                    $return['time'] = "8:00 AM-12:00 PM";
                } else {
                    $return['time'] = "1:00 PM-4:00 PM";
                }
            }
            

        }

        return $return;
    }

    public function update_sent_status(Request $request)
    {
        $update = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
            ->where('beneficiary_id', $request->beneficiary_id)
            ->update([
                'isSent' => $request->isSent,
            ]);

        return $update;
    }

    public function get_sed_id()
    {
        $data = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
            ->select('*')
            ->get();

        foreach ($data as $d) {
            $sed = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->select('sed_id')
                ->where('rsbsa_control_number', 'like', $d->rsbsa_control_no)
                ->where('isScheduled', 1)
                ->first();

            $update = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
                ->where('beneficiary_id', $d->beneficiary_id)
                ->update([
                    'sed_id_fk' => $sed->sed_id,
                ]);
        }
    }

    public function deleteBatch(Request $request)
    {
        $data = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
            ->select('*')
            ->where('is_waived', 0)
            ->where('isSent', 0)
            ->where('batch_code', $request->batch)
            ->get();

        $checkBatch = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
            ->select('*')
            ->addSelect(DB::raw("CONCAT(firstname,' ',middname,' ',lastname) as fullname"))
            ->where('is_waived', 0)
            ->where('isSent', 1)
            ->where('batch_code', $request->batch)
            ->first();

        if (count($checkBatch) > 0) {
            return ['status' => 0, 'message' => 'Batch have SMS sent status.'];
        }

        if (count($data) > 0) {

            DB::beginTransaction();
            try {
                foreach ($data as $d) {
                    $update = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                        ->where('isScheduled', 1)
                        ->where('sed_id', $d->sed_id_fk)
                        ->update([
                            'isScheduled' => 0,
                        ]);

                    if ($update) {
                        $d = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
                            ->where('beneficiary_id', $d->beneficiary_id)
                            ->delete();
                    }
                }

                $update = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_scheduler')
                    ->where('sched_batch', $request->batch)
                    ->update([
                        'isDeleted' => date('Y-m-d H:i:s'),
                    ]);

                DB::commit();
                return ['status' => 1, 'message' => 'Deleted batch successfully.'];
            } catch (\Exception $e) {
                DB::rollback();
                // Session::flash('error', 'Error adding user.');
                return ['status' => 0, 'message' => 'Error deleting batch.' . $e];
            }

        }

        return $return;
    }
}
