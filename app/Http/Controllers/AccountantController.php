<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use DB;
use Auth;

class AccountantController extends Controller

{
    public function index(){

        $coops = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('isActive', 1)->orderBy('coopName')
            ->get();

        $select2_data = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('isActive', 1)->orderBy('coopName')
            ->get();

        // $regions = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection_mirror.tbl_delivery")
        //     ->where('region', '!=', '')
        //     ->groupBy('region')
        //     ->orderBy('region')
        //     ->get();

        $data = $this->get_top_10_deliveries();
        
            return view("payment_accountant.index")
            ->with("data", $data)
            // ->with("regions", $regions)
            ->with("select2_data", $select2_data)
            ->with("coops", $coops);
        }

        public function get_top_10_deliveries() {
            $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                    ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                    ->where('a.has_rla', '=', '1')
                    ->where('moa_number','=','MOA-DS22-06-19')
                    ->groupBy('a.batchTicketNumber')
                    ->orderBy('a.dateCreated', 'decs')
                    ->get();
                    return $data;
        }


        public function load_sg_deliveries(Request $request){

            $sg = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')
                ->where('isActive', 1)
                ->where('current_moa',$request->current_moa)
                ->orderBy('coopName')
                ->get();

            $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                // ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                ->select('a.*','b.*',DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'))
                ->where('a.has_rla', '=', '1')
                ->where('a.moa_number','=',$request->current_moa)
                ->groupBy('a.batchTicketNumber')
                ->orderBy('a.dateCreated','decs')
                ->get();
            
            // $status = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status AS a')

            

            return compact('data','sg');

        }
        
        public function filter(Request $request) {

            

            $region = $request->region;
            $province = $request->province;
            $municipality = $request->municipality;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $attachement_status = $request-> attachement_status;
            $current_moa = $request->current_moa;
            
            // coop only
            // if($current_moa != "" && $start_date == "" && $end_date == "" && $region == "" && $province == "" && $municipality == "" && $attachement_status == "0"){
                
            //     $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
            //                 ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
            //                 ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
            //                 ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
            //                 ->where('a.has_rla', '=', '1')
            //                 ->where('a.moa_number','=',$current_moa)
            //                 ->groupBy('a.batchTicketNumber')
            //                 ->orderBy('a.dateCreated','decs')
            //                 ->get();

            // // coop + status
            if($current_moa != "" && $start_date == "" && $end_date == "" && $region == "" && $province == "" && $municipality == "" && $attachement_status != "0"){                  
                // dd($request->attachement_status);
                if($attachement_status == '6'){
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                            ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                            ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                            ->where('a.has_rla', '=', '1')
                            ->where('a.moa_number','=',$current_moa)
                            ->where('c.status','=',null)
                            ->groupBy('a.batchTicketNumber')
                            ->orderBy('a.dateCreated','decs')
                            ->get();                            
                }else{
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                            ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                            ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                            ->where('a.has_rla', '=', '1')
                            ->where('a.moa_number','=',$current_moa)
                            ->where('c.status', '=', $attachement_status)
                            ->groupBy('a.batchTicketNumber')
                            ->orderBy('a.dateCreated','decs')
                            ->get();    

                }

            // coop date
            }if($current_moa != "" && $start_date != "" && $end_date != "" && $region == "" && $province == "" && $municipality == "" && $attachement_status == "0"){
                $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                            ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                            ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                            ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                            ->where('a.moa_number','=',$current_moa)
                            ->where('a.has_rla', '=', '1')
                            ->groupBy('a.batchTicketNumber')
                            ->orderBy('a.dateCreated','decs')
                            ->get();

             // coop stat date
            }if($current_moa != "" && $start_date != "" && $end_date != "" && $region == "" && $province == "" && $municipality == "" && $attachement_status != "0"){
                if($attachement_status == '6'){
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                                ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                                ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                                ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                                ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                                ->where('a.moa_number','=',$current_moa)
                                ->where('c.status','=',null)
                                ->where('a.has_rla', '=', '1')
                                ->groupBy('a.batchTicketNumber')
                                ->orderBy('a.dateCreated','decs')
                                ->get();
                }else{
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                                ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                                ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                                ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                                ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                                ->where('a.moa_number','=',$current_moa)
                                ->where('c.status', '=', $attachement_status)
                                ->where('a.has_rla', '=', '1')
                                ->groupBy('a.batchTicketNumber')
                                ->orderBy('a.dateCreated','decs')
                                ->get();

                }

            // coop date reg 
            }if($current_moa != "" && $start_date != "" && $end_date != "" && $region != "" && $province == "" && $municipality == "" && $attachement_status == "0"){   
                $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                            ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                            ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                            ->where('a.has_rla', '=', '1')
                            ->where('a.moa_number','=',$current_moa)
                            ->where('a.region','LIKE','%'.$region.'%')
                            ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                            ->groupBy('a.batchTicketNumber')
                            ->orderBy('a.dateCreated','decs')
                            ->get();
            
            // coop date reg stat              
            }if($current_moa != "" && $start_date != "" && $end_date != "" && $region != "" && $province == "" && $municipality == "" && $attachement_status != "0"){   
                if($attachement_status == '6'){
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                                ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                                ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                                ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                                ->where('a.has_rla', '=', '1')
                                ->where('a.moa_number','=',$current_moa)
                                ->where('a.region','LIKE','%'.$region.'%')
                                ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                                ->where('c.status','=',null)
                                ->groupBy('a.batchTicketNumber')
                                ->orderBy('a.dateCreated','decs')
                                ->get();

                }else{
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                                ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                                ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                                ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                                ->where('a.has_rla', '=', '1')
                                ->where('a.moa_number','=',$current_moa)
                                ->where('a.region','LIKE','%'.$region.'%')
                                ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                                ->where('c.status', '=', $attachement_status)
                                ->groupBy('a.batchTicketNumber')
                                ->orderBy('a.dateCreated','decs')
                                ->get();
                }
            //  coop date reg prov 
            }if($current_moa != "" && $start_date != "" && $end_date != "" && $region != "" && $province != "" && $municipality == "" && $attachement_status == "0"){   
                $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                            ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                            ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                            ->where('a.has_rla', '=', '1')
                            ->where('a.moa_number','=',$current_moa)
                            ->where('a.region','LIKE','%'.$region.'%')
                            ->where('a.province','LIKE','%'.$province.'%')
                            ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                            ->groupBy('a.batchTicketNumber')
                            ->orderBy('a.dateCreated','decs')
                            ->get();

            //  coop date reg prov stat
            }if($current_moa != "" && $start_date != "" && $end_date != "" && $region != "" && $province != "" && $municipality == "" && $attachement_status != "0"){  
                if($attachement_status == '6'){
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                            ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                            ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                            ->where('a.has_rla', '=', '1')
                            ->where('a.moa_number','=',$current_moa)
                            ->where('a.region','LIKE','%'.$region.'%')
                            ->where('a.province','LIKE','%'.$province.'%')
                            ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                            ->where('c.status','=',null)
                            ->groupBy('a.batchTicketNumber')
                            ->orderBy('a.dateCreated','decs')
                            ->get();
                }else{
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                            ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                            ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                            ->where('a.has_rla', '=', '1')
                            ->where('a.moa_number','=',$current_moa)
                            ->where('a.region','LIKE','%'.$region.'%')
                            ->where('a.province','LIKE','%'.$province.'%')
                            ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                            ->where('c.status', '=', $attachement_status)
                            ->groupBy('a.batchTicketNumber')
                            ->orderBy('a.dateCreated','decs')
                            ->get();
                }

            // coop date reg prov muni
            }if($current_moa != "" && $start_date != "" && $end_date != "" && $region != "" && $province != "" && $municipality != "" && $attachement_status == "0"){   
                $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                            ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                            ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                            ->where('a.has_rla', '=', '1')
                            ->where('a.moa_number','=',$current_moa)
                            ->where('a.region','LIKE','%'.$region.'%')
                            ->where('a.province','LIKE','%'.$province.'%')
                            ->where('a.municipality','LIKE','%'.$municipality.'%')
                            ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                            ->groupBy('a.batchTicketNumber')
                            ->orderBy('a.dateCreated','decs')
                            ->get();

            // coop date reg prov muni stat
            }if($current_moa != "" && $start_date != "" && $end_date != "" && $region != "" && $province != "" && $municipality != "" && $attachement_status != "0"){  
                if($attachement_status == '6'){
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                            ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                            ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                            ->where('a.has_rla', '=', '1')
                            ->where('a.moa_number','=',$current_moa)
                            ->where('a.region','LIKE','%'.$region.'%')
                            ->where('a.province','LIKE','%'.$province.'%')
                            ->where('a.municipality','LIKE','%'.$municipality.'%')
                            ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                            ->where('c.status','=',null)
                            ->groupBy('a.batchTicketNumber')
                            ->orderBy('a.dateCreated','decs')
                            ->get();
                }else{
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                            ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                            ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                            ->where('a.has_rla', '=', '1')
                            ->where('a.moa_number','=',$current_moa)
                            ->where('a.region','LIKE','%'.$region.'%')
                            ->where('a.province','LIKE','%'.$province.'%')
                            ->where('a.municipality','LIKE','%'.$municipality.'%')
                            ->whereRaw("DATE(a.dateCreated) between DATE('".$start_date."') and DATE('".$end_date."')")
                            ->where('c.status', '=', $attachement_status)
                            ->groupBy('a.batchTicketNumber')
                            ->orderBy('a.dateCreated','decs')
                            ->get();
                }
                
            // coop reg prov muni
            }if($current_moa != "" && $region != "" && $start_date == "" && $end_date == "" && $province != "" && $municipality != "" && $attachement_status == "0"){  
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                            ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                            ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                            ->where('a.has_rla', '=', '1')
                            ->where('a.moa_number','=',$current_moa)
                            ->where('a.region','LIKE','%'.$region.'%')
                            ->where('a.province','LIKE','%'.$province.'%')
                            ->where('a.municipality','LIKE','%'.$municipality.'%')
                            ->groupBy('a.batchTicketNumber')
                            ->orderBy('a.dateCreated','decs')
                            ->get();

            // coop reg prov muni stat
            }if($current_moa != "" && $region != "" && $start_date == "" && $end_date == "" && $province != "" && $municipality != "" && $attachement_status != "0"){  
                if($attachement_status == '6'){
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                        ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                        ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                        ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                        ->where('a.has_rla', '=', '1')
                        ->where('a.moa_number','=',$current_moa)
                        ->where('a.region','LIKE','%'.$region.'%')
                        ->where('a.province','LIKE','%'.$province.'%')
                        ->where('a.municipality','LIKE','%'.$municipality.'%')
                        ->where('c.status','=',null)
                        ->groupBy('a.batchTicketNumber')
                        ->orderBy('a.dateCreated','decs')
                        ->get();

                }else{
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                        ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                        ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                        ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                        ->where('a.has_rla', '=', '1')
                        ->where('a.moa_number','=',$current_moa)
                        ->where('a.region','LIKE','%'.$region.'%')
                        ->where('a.province','LIKE','%'.$province.'%')
                        ->where('a.municipality','LIKE','%'.$municipality.'%')
                        ->where('c.status', '=', $attachement_status)
                        ->groupBy('a.batchTicketNumber')
                        ->orderBy('a.dateCreated','decs')
                        ->get();

                }
                
       
             // coop reg prov 
            }if($current_moa != "" && $region != "" && $start_date == "" && $end_date == "" && $province != "" && $municipality == "" && $attachement_status == "0"){  
                $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                        ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                        ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                        ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                        ->where('a.has_rla', '=', '1')
                        ->where('a.moa_number','=',$current_moa)
                        ->where('a.region','LIKE','%'.$region.'%')
                        ->where('a.province','LIKE','%'.$province.'%')
                        ->groupBy('a.batchTicketNumber')
                        ->orderBy('a.dateCreated','decs')
                        ->get();

            // coop reg prov stat
            }if($current_moa != "" && $region != "" && $start_date == "" && $end_date == "" && $province != "" && $municipality == "" && $attachement_status != "0"){
                if($attachement_status == '6'){
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                        ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                        ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                        ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                        ->where('a.has_rla', '=', '1')
                        ->where('a.moa_number','=',$current_moa)
                        ->where('a.region','LIKE','%'.$region.'%')
                        ->where('a.province','LIKE','%'.$province.'%')
                        ->where('c.status','=',null)
                        ->groupBy('a.batchTicketNumber')
                        ->orderBy('a.dateCreated','decs')
                        ->get();

                }else{
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                        ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                        ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                        ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                        ->where('a.has_rla', '=', '1')
                        ->where('a.moa_number','=',$current_moa)
                        ->where('a.region','LIKE','%'.$region.'%')
                        ->where('a.province','LIKE','%'.$province.'%')
                        ->where('c.status', '=', $attachement_status)
                        ->groupBy('a.batchTicketNumber')
                        ->orderBy('a.dateCreated','decs')
                        ->get();
                }

            // coop reg
            }if($current_moa != "" && $region != "" && $start_date == "" && $end_date == "" && $province == "" && $municipality == "" && $attachement_status == "0"){  
                $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                        ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                        ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                        ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                        ->where('a.has_rla', '=', '1')
                        ->where('a.moa_number','=',$current_moa)
                        ->where('a.region','LIKE','%'.$region.'%')
                        ->groupBy('a.batchTicketNumber')
                        ->orderBy('a.dateCreated','decs')
                        ->get();

            // coop reg stat
            }if($current_moa != "" && $region != "" && $start_date == "" && $end_date == "" && $province == "" && $municipality == "" && $attachement_status != "0"){  
                if($attachement_status == '6'){
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                        ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                        ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                        ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                        ->where('a.has_rla', '=', '1')
                        ->where('a.moa_number','=',$current_moa)
                        ->where('a.region','LIKE','%'.$region.'%')
                        ->where('c.status','=',null)
                        ->groupBy('a.batchTicketNumber')
                        ->orderBy('a.dateCreated','decs')
                        ->get();
                }else{
                    $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery AS a')
                        ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.current_moa', '=','a.moa_number')
                        ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_payments_status as c','c.batchTicketNumber', '=', 'a.batchTicketNumber')
                        ->select('a.*', 'b.*', 'c.status as batch_status', DB::raw('DATE_FORMAT(a.dateCreated, "%b %d, %Y") as dateCreated_new'), DB::raw('sum(a.totalBagCount) as sum_total_bags'))
                        ->where('a.has_rla', '=', '1')
                        ->where('a.moa_number','=',$current_moa)
                        ->where('a.region','LIKE','%'.$region.'%')
                        ->where('c.status', '=', $attachement_status)
                        ->groupBy('a.batchTicketNumber')
                        ->orderBy('a.dateCreated','decs')
                        ->get();

                }
                
            }

            $sg = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')
                ->where('isActive', 1)
                ->where('current_moa',$current_moa)
                ->orderBy('coopName')
                ->get();

            $data_count = count($data);

        return compact('data','sg','data_count');
        }

        public function for_assesment(Request $request){

            $final=array();
              $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery as a')
                      ->where('has_rla', '=', '1')
                      ->where('a.batchTicketNumber', '=', $request->batch_ticket)
                      ->get();
                     
                      foreach ($data as $value) {
                          $path="";
                          $remarksDRO="";
                          $remarksCES="";
                          $status="";
                          $stat_color="";
                          $is_batch="";
                          $is_batch_type="";
                          $seedTags = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_payments_attachements')
                              ->where('tbl_payments_attachements.seed_tag', $value->seedTag)
                              // ->where('tbl_payments_attachements.is_seed_tag','=','1')
                              ->orderBy('id', 'DECS')
                              ->first();
                           
                          if(count($seedTags )>0){
                              $path=$seedTags->file_path;
                              $remarksDRO=$seedTags->remarks_dro;
                              $remarksCES=$seedTags->remarks_ces;
      
                                  if($seedTags->status == 0){
                                      $status = 'For Assesment';
                                      $stat_color ='btn-warning btn-xs';
                                      
                                  }elseif($seedTags->status == 1){
                                      $status = 'Passed';
                                      $stat_color ='btn-success btn-xs';
                                  }elseif($seedTags->status == 2){
                                      $status = 'Failed';
                                      $stat_color ='btn-danger btn-xs';
                                  }elseif($seedTags->status ==''){
                                      $status = 'For upload';
                                      $stat_color ='btn-warning btn-xs';
      
                                  }
                         
                              
                          }
                          $tmp =[
                              'seed_tag'=>$value->seedTag,
                              'variety'=>$value->seedVariety,
                              'volume'=>$value->totalBagCount,
                              'path'=>$path,
                              'remarks_dro'=>$remarksDRO,
                              'remarks_CES'=>$remarksCES,
                              'batch_number'=>$request->batch_ticket,
                              'status'=>$status,
                              'stat_color'=> $stat_color,
                              
                              'is_batch'=> $is_batch,
                              'is_batch_type'=> $is_batch_type
                       
                            ];
                      array_push($final,$tmp);
                      }
              return $final;
          }
}
