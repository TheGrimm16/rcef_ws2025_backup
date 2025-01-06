<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use Auth;
use ZipArchive;
use Illuminate\Support\Facades\File;
use RecursiveIteratorIterator;
use Yajra\Datatables\Datatables;



class DvFormatterController extends Controller
{
 
    public function index(){

        $regions = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
            ->select('region')
            ->groupby('region')
            ->get();

        $coop_view=array();

        $coops = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('isActive', 1)
          ->orderBy('coopName')
            ->get();

            foreach ($coops as $coop) {

              $total_batch = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                    ->where('moa_number', '=', $coop->current_moa)
                    // ->where('is_dv_tag', '=','0')
                    // ->where('transferCategory', '!=','P') 
                    ->groupby('batchTicketNumber')
                    ->get();

              $total_batch = count($total_batch);

              $untag_batch_count = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                    ->where('moa_number', '=', $coop->current_moa)
                    ->where('is_dv_tag', '=','0')
                    // ->where('transferCategory', '!=','P') 
                    ->groupby('batchTicketNumber')
                    ->get();

              $untag_batch_count = count($untag_batch_count);

                $tagged_batch_count = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                    ->where('moa_number', '=', $coop->current_moa)
                    ->where('is_dv_tag', '!=','0')
                    // ->where('transferCategory', '!=','P')
                    ->groupby('batchTicketNumber')
                    ->get();

                    $tagged_batch_count = count($tagged_batch_count);

                $with_no_iar = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.with_no_iar')
                    ->where('moa_number', '=', $coop->current_moa)
                    ->groupby('batchTicketNumber')
                    ->get();

                    $with_no_iar = count($with_no_iar);

                $inspected_bags = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                  // ->where('transferCategory', '!=','P')
                    ->where('moa_number', '=', $coop->current_moa)
                    ->sum('totalBagCount');

                $total_deliveries = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
                    ->where('coopAccreditation', '=', $coop->accreditation_no)
                    ->where('is_cancelled', '!=',1)
                    ->sum('totalBagCount');

                    // $untag_batch_count_per = $untag_batch_count / $total_batch * 100;

                    $tmp =[
                        'coopName'=>$coop->coopName,
                        'acronym'=>$coop->acronym,
                        'current_moa'=>$coop->current_moa,
                        'accreditation'=>$coop->accreditation_no,
                        'untag_batch_count'=>$untag_batch_count,
                        'tagged_batch_count'=>$tagged_batch_count,
                        'address'=>$coop->full_address,
                        'with_no_iar'=>$with_no_iar,
                        'inspected_bags'=>number_format($inspected_bags),
                        'total_deliveries'=>number_format($total_deliveries),
                        // 'untag_batch_count_per'=> $untag_batch_count / $total_batch * 100,
                        // 'tagged_batch_count_per'=>$tagged_batch_count / $total_batch * 100,
                        // 'with_no_iar_per'=>$with_no_iar / $total_batch * 100,
                        // 'inspected_bags_per'=>$inspected_bags / $total_batch * 100

                        
                          
                      ];

                array_push($coop_view,$tmp);
                
            }

        $select2_data = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('isActive', 1)->orderBy('coopName')
            ->get();

        // $data = $this->get_top_10_deliveries();
        
            return view("dv_formatter.home")
            // ->with("data", $data)
            ->with("regions", $regions)
            ->with("select2_data", $select2_data)
            ->with("coop_view", $coop_view)
            ->with("coops", $coops);
    }


        public function iar_tbl_home(Request $request){
            $table_data = array();
            $tbl_actual_delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                ->where('moa_number', $request->current_moa)
                // ->where('transferCategory', '!=','P')
                ->where('is_dv_tag',0)
                ->groupBy('batchTicketNumber')
                ->orderBy('dateCreated', 'DESC')
                ->get();
    
            foreach($tbl_actual_delivery as $tbl_actual_delivery_row){
                $iar_numnber = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')
                    ->where('batchTicketNumber', $tbl_actual_delivery_row->batchTicketNumber)
                    ->orderBy('logsId', 'DESC')
                    ->first();

               $tbl_delivery_delivery_date = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
                    ->where(['batchTicketNumber' => $tbl_actual_delivery_row->batchTicketNumber])
                    ->groupBy('batchTicketNumber')
                    ->first();

                    

                    if(isset($tbl_delivery_delivery_date)){
                      $tbl_delivery_delivery_date = $tbl_delivery_delivery_date->deliveryDate;

                    }else{
                      // transfer seeds
                      $tbl_delivery_delivery_date = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                        ->where(['batchTicketNumber' => $tbl_actual_delivery_row->batchTicketNumber])
                        ->groupBy('batchTicketNumber')
                        ->first();

                   
                          $tbl_delivery_delivery_date = $tbl_delivery_delivery_date->dateCreated;
        
                    }

                // return $tbl_delivery_delivery_date->deliveryDate;
    
                $tbl_actual_delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                    // ->where('transferCategory', '!=','P')
                    ->where([
                        'batchTicketNumber' => $tbl_actual_delivery_row->batchTicketNumber,
                        'has_rla' => 0
                    ])
                    ->groupBy('batchTicketNumber')
                    ->count();


                $total_bag_count = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                    ->where([
                        'batchTicketNumber' => $tbl_actual_delivery_row->batchTicketNumber,
                        // 'has_rla' => 0
                    ])
                    ->sum('totalBagCount');
    
                $tbl_actual_delivery_count = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                    ->where([
                        'batchTicketNumber' => $tbl_actual_delivery_row->batchTicketNumber,
                    ])
                    ->groupBy('batchTicketNumber')
                    ->count();
    
                if(count($iar_numnber) > 0){
                    $iar_numnber = $iar_numnber->iarCode;
                }else{
                    $iar_numnber = "N/A";
                }

                // dd($tbl_delivery_delivery_date);
                if($tbl_actual_delivery_count > 0){
                    array_push($table_data, array(
                        "iar_number" => $iar_numnber,
                        "batch_code" => $tbl_actual_delivery_row->batchTicketNumber,
                        "region" => $tbl_actual_delivery_row->region,
                        "province" => $tbl_actual_delivery_row->province,
                        "municipality" => $tbl_actual_delivery_row->municipality,
                        "dop" => $tbl_actual_delivery_row->dropOffPoint,
                        // "total_bags_delivered"=> $tbl_actual_delivery_2,
                        "total_bags_inspected"=> $total_bag_count,
                        "delivery_date" => date("F j, Y", strtotime($tbl_delivery_delivery_date))
                    ));
                }
               
            }
            
            $table_data = collect($table_data);
            return Datatables::of($table_data)

            ->addColumn('select', function($table_data){ 
              if($table_data['iar_number'] == "N/A")   {
                return '<input type="checkbox" class="checkbox_all form-group check_all" name="" disabled>';  
              }else{
                return '<input type="checkbox" class="checkbox_all form-group check_all" id="check_'.$table_data['batch_code'].'" name = "selected_batch" value="'.$table_data['batch_code'].'" data-id="'.$table_data['iar_number'].'">';
                 
              }
                             
                
              }) 

            ->make(true);
        }

        public function update_status_overall(Request $request){
                 
            DB::beginTransaction();
            try{

                $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                ->whereIn('batchTicketNumber', $request->checkboxValues)
                ->update([
                    'is_dv_tag' => 1,
                    'dv_control_no' => $request->dv_no
                ]);

                DB::commit();
                    return 1;
            } catch (\Exception $e) {
                    DB::rollback();
                    }
            
        }

          

          public function get_coop_details(Request $request){
            // $coop_view=array();

            // $regions = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
            // ->select('region')
            // ->groupby('region')
            // ->get();

            $coop= DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')
            ->where('current_moa',$request->current_moa )
            ->first();

            $untag_batch_count = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                    ->where('moa_number', '=', $coop->current_moa)
                    ->where('is_dv_tag', '=','0')
                    // ->where('transferCategory', '!=','P') 
                    ->groupby('batchTicketNumber')
                    ->get();

              $untag_batch_count = count($untag_batch_count);

              $tagged_batch_count = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
              ->where('moa_number', '=', $coop->current_moa)
              ->where('is_dv_tag', '!=','0')
              // ->where('transferCategory', '!=','P')
              ->groupby('batchTicketNumber')
              ->get();

              $tagged_batch_count = count($tagged_batch_count);

              $with_no_iar = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.with_no_iar')
              ->where('moa_number', '=', $coop->current_moa)
              ->groupby('batchTicketNumber')
              ->get();

              $with_no_iar = count($with_no_iar);

            $inspected_bags = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                ->where('moa_number', '=', $request->current_moa)
                ->sum('totalBagCount');

            $total_deliveries = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
                ->where('coopAccreditation', '=', $coop->accreditation_no)
                ->where('is_cancelled', '!=',1)
                ->sum('totalBagCount');


            $coop_view =[
                'coopName'=>$coop->coopName,
                'acronym'=>$coop->acronym,
                'current_moa'=>$coop->current_moa,
                'accreditation'=>$coop->accreditation_no,
                'untag_batch_count'=>$untag_batch_count,
                'tagged_batch_count'=>$tagged_batch_count,
                'address'=>$coop->full_address,
                'with_no_iar'=>$with_no_iar,
                'inspected_bags'=>number_format($inspected_bags),
                'total_deliveries'=>number_format($total_deliveries)
                
                  
              ];

            // array_push($coop_view,$tmp);
             
          return $coop_view;
        
            
          }


          public function get_coops2(Request $request){
     

            $data = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')
                ->where('region_name', 'LIKE', $request->region)
                ->groupby('coop_name')
                ->get();


                $return_str= '';
                foreach($data as $row){
                    $return_str .= "<option value='$row->accreditation_no'>$row->coop_name</option>";
                }
                return $return_str;

          }

        

          public function search_coop(Request $request){
     
            $coop= DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')
            ->where('accreditation_no',$request->coop_accre )
            ->first();

            $untag_batch_count = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                    ->where('moa_number', '=', $coop->current_moa)
                    ->where('is_dv_tag', '=','0')
                    // ->where('transferCategory', '!=','P') 
                    ->groupby('batchTicketNumber')
                    ->get();

              $untag_batch_count = count($untag_batch_count);

              $tagged_batch_count = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
              ->where('moa_number', '=', $coop->current_moa)
              ->where('is_dv_tag', '!=','0')
              // ->where('transferCategory', '!=','P')
              ->groupby('batchTicketNumber')
              ->get();

              $tagged_batch_count = count($tagged_batch_count);

              $with_no_iar = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.with_no_iar')
              ->where('moa_number', '=', $coop->current_moa)
              ->groupby('batchTicketNumber')
              ->get();

              $with_no_iar = count($with_no_iar);

            $inspected_bags = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                ->where('moa_number', '=', $coop->current_moa)
                ->sum('totalBagCount');

            $total_deliveries = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
                ->where('coopAccreditation', '=', $coop->accreditation_no)
                ->where('is_cancelled', '!=',1)
                ->sum('totalBagCount');


            $coop_view =[
                'coopName'=>$coop->coopName,
                'acronym'=>$coop->acronym,
                'current_moa'=>$coop->current_moa,
                'accreditation'=>$coop->accreditation_no,
                'untag_batch_count'=>$untag_batch_count,
                'tagged_batch_count'=>$tagged_batch_count,
                'address'=>$coop->full_address,
                'with_no_iar'=>$with_no_iar,
                'inspected_bags'=>number_format($inspected_bags),
                'total_deliveries'=>number_format($total_deliveries)
                
                  
              ];

            // array_push($coop_view,$tmp);
             
          return $coop_view;
        
            
          }








          public function region_list(Request $request){

             $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
              ->select('region')
              ->groupby('region')
              ->get();

                $return_str= '';
                foreach($data as $row){
                    $return_str .= "<option value='$row->region'>$row->region</option>";
                }
                return $return_str;

          }

          


          public function particularsPreview(Request $request){
            $iar_number = json_encode($request->iar_number);


            $deliveryDate_data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
            ->select('deliveryDate')
            ->whereIn('batchTicketNumber', $request->checkboxValues)
            ->groupBy('batchTicketNumber')
            ->get();

            $min_date = json_encode(min($deliveryDate_data));
            $max_date = json_encode(max($deliveryDate_data));


            $min_date = explode(' ' ,$min_date);
            $max_date = explode(' ' ,$max_date);
        

            // yy/mm/dd
            $min_date = explode('-' ,$min_date['0']);
            $max_date = explode('-' ,$max_date['0']);
            $max_date['0'] = str_ireplace('{"deliveryDate":"', '', $max_date['0']);
            $min_date['0'] = str_ireplace('{"deliveryDate":"', '', $min_date['0']);

            

            if($min_date['1'] == $max_date['1'] && $min_date['0'] == $max_date['0'] ){

                switch ($min_date['1']) {
                    case '01':
                      $min_date['1'] = "Jan";
                      break;
                    case "02":
                      $min_date['1'] = "Feb";
                      break;
                    case '03':
                        $min_date['1'] = "March";
                      break;
                    case '04':
                        $min_date['1'] = "April";
                      break;
                    case '05':
                        $min_date['1'] = "May";
                      break;
                    case '06':
                        $min_date['1'] = "June";
                      break;
                    case '07':
                        $min_date['1'] = "Jully";
                      break;
                    case '08':
                        $min_date['1'] = "Aug";
                      break;
                    case '09':
                        $min_date['1'] = "Sept";
                      break;
                    case '10':
                        $min_date['1'] = "Oct";
                      break;
                    case '11':
                        $min_date['1'] = "Nov";
                      break;
                    case "12":
                      $min_date['1'] = "Dec";
                      break;
                    default:
                      $min_date['1'] = "Sept";
                  }

                  if($min_date['2'] == $max_date['2']){
                    $deliveryDate = $min_date['1'].' '.$min_date['2'].', '.$max_date['0'];
                  }else{
                    $deliveryDate = $min_date['1'].' '.$min_date['2'].'-'.$max_date['2'].', '.$max_date['0'];
                  }
              
            }else{
              

                switch ($min_date['1']) {
                    case '01':
                      $min_date['1'] = "Jan";
                      break;
                    case "02":
                      $min_date['1'] = "Feb";
                      break;
                    case '03':
                        $min_date['1'] = "March";
                      break;
                    case '04':
                        $min_date['1'] = "April";
                      break;
                    case '05':
                        $min_date['1'] = "May";
                      break;
                    case '06':
                        $min_date['1'] = "June";
                      break;
                    case '07':
                        $min_date['1'] = "Jully";
                      break;
                    case '08':
                        $min_date['1'] = "Aug";
                      break;
                    case '09':
                        $min_date['1'] = "Sept";
                      break;
                    case '10':
                        $min_date['1'] = "Oct";
                      break;
                    case '11':
                        $min_date['1'] = "Nov";
                      break;
                    case "12":
                      $min_date['1'] = "Dec";
                      break;
                    default:
                      $min_date['1'] = "Sept";
                  }

                  switch ($max_date['1']) {
                    case '01':
                      $max_date['1'] = "Jan";
                      break;
                    case "02":
                      $max_date['1'] = "Feb";
                      break;
                    case '03':
                        $max_date['1'] = "March";
                      break;
                    case '04':
                        $max_date['1'] = "April";
                      break;
                    case '05':
                        $max_date['1'] = "May";
                      break;
                    case '06':
                        $max_date['1'] = "June";
                      break;
                    case '07':
                        $max_date['1'] = "Jully";
                      break;
                    case '08':
                        $max_date['1'] = "Aug";
                      break;
                    case '09':
                        $max_date['1'] = "Sept";
                      break;
                    case '10':
                        $max_date['1'] = "Oct";
                      break;
                    case '11':
                        $max_date['1'] = "Nov";
                      break;
                    case "12":
                      $max_date['1'] = "Dec";
                      break;
                    default:
                      $max_date['1'] = "Sept";
                  }

                   $deliveryDate = $min_date['1'].' '.$min_date['2'].'-'.$max_date['1'].' '.$max_date['2'].', '.$max_date['0'];
            }
             

            $tbl_delivery_data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                ->select(DB::raw('SUM(totalBagCount) as total_bags'), 'region', 'province', 'municipality')
                ->whereIn('batchTicketNumber', $request->checkboxValues)
                ->first();
               
    
            $cost = $tbl_delivery_data->total_bags * 760;
            $retention= $cost * 0.01; 
            $particulars = "Payment for ".number_format($tbl_delivery_data->total_bags)." bags of certified seeds for 2023 DS as per seed delivery dtd ".$deliveryDate." amounting to ".number_format($cost,2,".",",")." | Attached IAR: ".$iar_number;
            
            $particulars = str_ireplace('[', '', $particulars);
            $particulars = str_ireplace(']', '', $particulars);
            $particulars = str_ireplace('"', '', $particulars);
            
           
            return json_encode($particulars);
        }



        public function coop_seeds_stat(Request $request){


          $coop = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')
            ->where('accreditation_no', $request->coop_accre)
            ->first();

          $coop_id = DB::connection('seed_coop_db')->table('tbl_cooperatives')->where('accreditation_no', $request->coop_accre)->value('coopId');
          $commitments = DB::connection('seed_coop_db')->table('tbl_commitment_regional')->select('seed_variety', DB::raw("SUM(volume) as volume"))->where('coop_Id', $coop_id)->groupBy("seed_variety")->get();


          $confirmed_delivery_list = DB::connection('delivery_inspection_db')->table("tbl_delivery")
              ->select('batchTicketNumber', DB::raw("SUM(totalBagCount) as total_bags"))
              ->where('tbl_delivery.is_cancelled', '=', '0')
              ->where('tbl_delivery.coopAccreditation', '=', $request->coop_accre)
              ->where('tbl_delivery.isBuffer', "!=", 9)
              ->groupBy('batchTicketNumber')
              ->get();




          $inspected_list_tmp = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
            ->select('batchTicketNumber', DB::raw("SUM(totalBagCount) as total_bags_inspected"))
            ->where('isBuffer', "!=", 9)
            ->where('moa_number', '=', $coop->current_moa)
            ->get();


   
  
          $variety_list = array();
          $inspected_list = array();
          $delivered_list = array();
          $confirmed_list   = array();
  
          $total_commitment = 0;
          $total_delivered = 0;
          $total_inspected = 0;
  
          foreach($confirmed_delivery_list as $batch){
                    $total_delivered += $batch->total_bags;
              
          }

          foreach($inspected_list_tmp as $batch2){
            $total_inspected += $batch2->total_bags_inspected;
            
        }
  
          foreach($commitments as $row){
              $inspected_total = $this->compute_inspected_total($row->seed_variety, $coop->current_moa);  
              $delivered_total = $this->compute_confirmed_total($row->seed_variety, $request->coop_accre);
  
              array_push($variety_list, $row->seed_variety);
              // array_push($commitment_list, intval($row->volume));
              array_push($inspected_list, intval($inspected_total));
              array_push($delivered_list, intval($delivered_total));
  
              // $total_commitment += $row->volume;
              // $total_delivered += $variety_total;
          }
          
          $ret =  array(
              'variety_list' => $variety_list,
              // 'commitment_list' => $commitment_list,
              'inspected_list' => $inspected_list,
              'delivered_list' => $delivered_list,
              'total_inspected' => number_format($total_inspected),
              'total_delivered' => number_format($total_delivered),
              // 'total_confirmed' => number_format($total_confirmed),
              // 'confirmed_list' => $confirmed_list
          );
  
  
          return $ret;
  
      }


      public function compute_variety_total($deliveries, $seed_variety){
        $total_variety = 0;

        //dd($deliveries);
        foreach($deliveries as $row){
            $variety_count = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery as ad')
                ->where('ad.batchTicketNumber', "=", $row->batchTicketNumber)
                ->where('ad.seedVariety', "=", $seed_variety)
                ->sum('ad.totalBagCount');
            
            $total_variety += $variety_count;
        }

        return $total_variety;
    }


    public function compute_dalivered_total($deliveries, $seed_variety){
      $total_variety = 0;

      //dd($deliveries);
      foreach($deliveries as $row){
          $variety_count = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery as ad')
              ->where('ad.batchTicketNumber', "=", $row->batchTicketNumber)
              ->where('ad.seedVariety', "=", $seed_variety)
              ->sum('ad.totalBagCount');
          
          $total_variety += $variety_count;
      }

      return $total_variety;
  }


    public function compute_confirmed_total($seed_variety, $accreditation){
      $variety_count = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery as cd')
          ->where('cd.seedVariety', "=", $seed_variety)
          ->where('cd.coopAccreditation', "=", $accreditation)
          ->sum('totalBagCount');

      return $variety_count;
    }


    public function compute_inspected_total($seed_variety, $current_moa){
      $variety_count = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery as cd')
          ->where('cd.seedVariety', "=", $seed_variety)
          ->where('moa_number', '=', $current_moa)
          // ->where('cd.coopAccreditation', "=", $accreditation)
          ->sum('totalBagCount');

      return $variety_count;
    }


    public function coop_regional_stat(Request $request){
      $region_list = array();
      $variety_list = array();
      $delivered_list = array();
      $inspected_list = array();
      $total_commitment = 0;
      $total_delivered = 0;
      $series_arr = array();


      $coop_id = DB::connection('seed_coop_db')->table('tbl_cooperatives')->where('accreditation_no', $request->coop_accre)->value('coopId');
      //$commitments = DB::connection('seed_coop_db')->table('tbl_commitment')->where('coopID', $coop_id)->get();
      $commitments = DB::connection('seed_coop_db')->table('tbl_commitment_regional')->where('coop_Id', $coop_id)->groupBy("region_name")->orderBy("region_name", "ASC")->get();
     // dd($commitments);
     
      foreach ($commitments as $commitment_data) {
          $varietyList = DB::connection('seed_coop_db')->table('tbl_commitment_regional')->where('coop_Id', $coop_id)->where('region_name', $commitment_data->region_name)->groupBy("seed_variety")->get();
              foreach ($varietyList as $variety) {
                  if($commitment_data->region_name=="ANY Region"){$region="%";}else{$region=$commitment_data->region_name;}
                 // dd($region->regio);
                      $confirmed = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
                          ->select("batchTicketNumber")
                          ->where("region", 'like', $region)
                          ->where("seedVariety", $variety->seed_variety)
                          ->where('tbl_delivery.coopAccreditation', '=', $request->coop_accre)
                          ->where('tbl_delivery.is_cancelled', '=', '0')
                          ->where("tbl_delivery.isBuffer", "!=", 9)
                          ->groupBy("batchTicketNumber")
                          ->get();


                      $delivered = $this->compute_variety_total($confirmed, $variety->seed_variety);
                      $delivered1 = $this->compute_dalivered_total($confirmed, $variety->seed_variety);   
                     
                      // dd($delivered1);
                      
                      $commitment_value =DB::connection('seed_coop_db')->table('tbl_commitment_regional')->where('coop_Id', $coop_id)->where('region_name', $commitment_data->region_name)->where("seed_variety", $variety->seed_variety)->sum("volume");
                      
                      //$commitment_value = 0;

                      $total_commitment += $commitment_value;
                      $total_delivered += $delivered;

                     array_push($region_list, $commitment_data->region_name);
                     array_push($variety_list, $variety->seed_variety);
                     array_push($delivered_list, intval($delivered1));
                     array_push($inspected_list, $delivered);
              
                      array_push($series_arr,array(
                          "region" => $commitment_data->region_name,
                          "variety" => $variety->seed_variety,
                          "commitment" => $commitment_value,
                          "delivered" => $delivered
                      ));

              }
      }
   
      


//        dd($series_arr);

      $ret =  array(
          'region_list' => $region_list,
          'variety_list' => $variety_list,
          'delivered_list' => $delivered_list,
          'inspected_list' => $inspected_list,
          'total_commitment' => number_format($total_commitment),
          'total_delivered' => number_format($total_delivered),
          "series_arr" => $series_arr
      );
  
      return $ret;

  }


}
