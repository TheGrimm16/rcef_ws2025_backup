<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Session;
use Auth;
use Excel;

use Yajra\Datatables\Facades\Datatables;


class rlaMonitoring extends Controller
{

    public function rlaFinder(Request $request){
        //CHECK FROM LAST SEASON
            $table_data = array();  
            
        
        $ls_rla = DB::connection("ls_inspection_db")
                ->table("tbl_actual_delivery")
                ->where("seedTag", "LIKE", $request->lab."/".$request->lot)
                ->first();

            if($ls_rla != null){
                $seed_tag = explode("/", $ls_rla->seedTag);
                $coop_info = DB::connection("ls_inspection_db")
                        ->table("tbl_rla_details")
                        ->where("labNo",$seed_tag[0])
                        ->where("lotNo",$seed_tag[1])
                        ->first();
                
                if($coop_info != null){
                    $coop_name = $coop_info->coop_name;
                    $sg_name = $coop_info->sg_name;
                    $seedVariety = $coop_info->seedVariety;
                    $noOfBags = $coop_info->noOfBags;
                }else{
                    $coop_name = "-";
                    $sg_name = "-";
                    $seedVariety = "-";
                    $noOfBags = "-";
                }
                $batchTicketNumber = $ls_rla->batchTicketNumber;
                $labNo = $seed_tag[0];
                $lotNo = $seed_tag[1];
                

                if($ls_rla->isBuffer == 1){
                    $isBuffer = "Buffer for this Season";
                }elseif($ls_rla->isBuffer == 9){
                    $isBuffer = "Replacement Seeds";
                }else{
                    $isBuffer = "No";
                }

                if($ls_rla->is_hold == 2){
                    $last_season = "No Second Inspection";
                }elseif($ls_rla->is_hold == 1){
                    $last_season = "With The Same SeedTag that is under Retest";
                }elseif($ls_rla->is_hold == 0){
                    $last_season = "Ready To Transfer";
                }else{
                    $last_season = "-";
                }


               
                $tbl_actual = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
                        ->where("remarks","LIKE","%".$batchTicketNumber."%")
                        ->where("seedTag", $coop_info->labNo."/".$coop_info->lotNo)
                        ->first();
                        if($tbl_actual != null){
                            $curr_season = "Transferred";
                        }else{
                            $curr_season = "Not Yet Transferred";
                        }

                $breakdown = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_breakdown_buffer")
                  
                    ->where("seedTag", $coop_info->labNo."/".$coop_info->lotNo)
                    ->first();
                    
                    if($breakdown != null){
                        if($breakdown->category == "G"){
                            $second_inspect = "For Distribution";
                        }elseif($breakdown->category == "R"){
                            $second_inspect = "Rejected";
                        }elseif($breakdown->category == "T"){
                            $second_inspect = "For Retesting";
                        }elseif($breakdown->category == "D"){
                            $second_inspect =  "For Donation";
                        }elseif($breakdown->category == "P"){
                            $second_inspect = "For Replacement";
                        }else{
                            $second_inspect = "-";
                        }

                    }else{
                        $second_inspect = "No second Inspection";
                    }


                array_push($table_data,array(
                    "season_checked" => "Previous Season",
                    "coop_name" => $coop_name,
                    "sg_name" => $sg_name,
                    "batchTicketNumber" => $batchTicketNumber,
                    "labNo" => $request->lab,
                    "lotNo" => $request->lot,
                    "seedVariety" => $seedVariety,
                    "noOfBags" => $noOfBags,
                    "last_season" => $last_season,
                    "curr_season" => $curr_season,
                    "isBuffer" => $isBuffer,
                    "second_inspect" => $second_inspect,
                ));



                $season_checked = 0;
            }else{

                $coop_info = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_rla_details")
                        ->where("labNo",$request->lab)
                        ->where("lotNo",$request->lot)
                        ->first();
    
                if($coop_info != null){
                    $coop_name = $coop_info->coop_name;
                    $sg_name = $coop_info->sg_name;
                    $seedVariety = $coop_info->seedVariety;
                    $noOfBags = $coop_info->noOfBags;

                    $tbl_delivery = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
                        ->where("seedTag", $coop_info->labNo."/".$coop_info->lotNo)
                        ->where("is_cancelled", 0)
                        ->first();

                    if($tbl_delivery != null){
                        $tbl_actual = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
                        ->where("seedTag", $coop_info->labNo."/".$coop_info->lotNo)
                        ->first();
                        if($tbl_actual != null){
                            $curr_season = "Inspected";
                        }else{
                            $curr_season = "Not Yet Inspected";
                        }

                        if($tbl_delivery->isBuffer == 1){
                            $isBuffer = "Buffer for next Season";
                        }elseif($tbl_delivery->isBuffer == 9){
                            $isBuffer = "Replacement Seeds";
                        }else{
                            $isBuffer = "No";
                        }
                        array_push($table_data,array(
                            "season_checked" => "Current Season",
                            "coop_name" => $coop_name,
                            "sg_name" => $sg_name,
                            "batchTicketNumber" => $tbl_delivery->batchTicketNumber,
                            "labNo" => $request->lab,
                            "lotNo" => $request->lot,
                            "seedVariety" => $seedVariety,
                            "noOfBags" => $noOfBags,
                            "last_season" => "-",
                            "curr_season" => $curr_season,
                            "isBuffer" => $isBuffer,
                            "second_inspect" => "-",
                        ));


                    }else{
                        array_push($table_data,array(
                            "season_checked" => "Current Season",
                            "coop_name" => $coop_name,
                            "sg_name" => $sg_name,
                            "batchTicketNumber" => "-",
                            "labNo" => $request->lab,
                            "lotNo" => $request->lot,
                            "seedVariety" => $seedVariety,
                            "noOfBags" => $noOfBags,
                            "last_season" => "-",
                            "curr_season" => "Not Yet Confirmed",
                            "isBuffer" => "-",
                            "second_inspect" => "-",
                        ));
                    }




                }else{
                    // array_push($table_data,array(
                    //     "season_checked" => "Current Season",
                    //     "coop_name" => "No RLA Info for Current",
                    //     "sg_name" => "-",
                    //     "batchTicketNumber" => "-",
                    //     "labNo" => $request->lab,
                    //     "lotNo" => $request->lot,
                    //     "seedVariety" => "-",
                    //     "noOfBags" => "-",
                    //     "last_season" => "-",
                    //     "curr_season" => "-",
                    //     "isBuffer" => "-",
                    //     "second_inspect" => "-",
                    // ));
                }

                $season_checked = 1;
            }

        //CURRENT SEASON START
            if($season_checked == 0){
                $coop_info = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_rla_details")
                ->where("labNo",$request->lab)
                ->where("lotNo",$request->lot)
                ->first();
        
                if($coop_info != null){
                    $coop_name = $coop_info->coop_name;
                    $sg_name = $coop_info->sg_name;
                    $seedVariety = $coop_info->seedVariety;
                    $noOfBags = $coop_info->noOfBags;
        
                    $tbl_delivery = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
                        ->where("seedTag", $coop_info->labNo."/".$coop_info->lotNo)
                        ->where("is_cancelled", 0)
                        ->first();
        
                    if($tbl_delivery != null){
                        $tbl_actual = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
                        ->where("seedTag", $coop_info->labNo."/".$coop_info->lotNo)
                        ->first();
                        if($tbl_actual != null){
                            $curr_season = "Inspected";
                        }else{
                            $curr_season = "Not Yet Inspected";
                        }
        
                        if($tbl_delivery->isBuffer == 1){
                            $isBuffer = "Buffer for next Season";
                        }elseif($tbl_delivery->isBuffer == 9){
                            $isBuffer = "Replacement Seeds";
                        }else{
                            $isBuffer = "No";
                        }
                        array_push($table_data,array(
                            "season_checked" => "Current Season",
                            "coop_name" => $coop_name,
                            "sg_name" => $sg_name,
                            "batchTicketNumber" => $tbl_delivery->batchTicketNumber,
                            "labNo" => $request->lab,
                            "lotNo" => $request->lot,
                            "seedVariety" => $seedVariety,
                            "noOfBags" => $noOfBags,
                            "last_season" => "-",
                            "curr_season" => $curr_season,
                            "isBuffer" => $isBuffer,
                            "second_inspect" => "-",
                        ));
        
        
                    }else{
                        array_push($table_data,array(
                            "season_checked" => "Current Season",
                            "coop_name" => $coop_name,
                            "sg_name" => $sg_name,
                            "batchTicketNumber" => "-",
                            "labNo" => $request->lab,
                            "lotNo" => $request->lot,
                            "seedVariety" => $seedVariety,
                            "noOfBags" => $noOfBags,
                            "last_season" => "-",
                            "curr_season" => "Not Yet Confirmed",
                            "isBuffer" => "-",
                            "second_inspect" => "-",
                        ));
                    }
        
        
        
        
                }else{
                 
                }
            }
        
    


    




          

            $table_data = collect($table_data);

            return Datatables::of($table_data)
                ->make(true);


             



    }

    public function home(){
        $coop_list = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")
            ->select("coopName", "accreditation_no", "current_moa")
            ->orderBy("coopName", "ASC")
            ->get();

            return view("dashboard.rla_dashboard")
                ->with("coop_list", $coop_list);
    }

    public function homeMissing(){
        $coop_list = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")
            ->select("coopName", "accreditation_no", "current_moa")
            ->orderBy("coopName", "ASC")
            ->get();

            return view("dashboard.rla_dashboardMissing")
                ->with("coop_list", $coop_list);
    }

    public function table_data(Request $request){
        if($request->accre == "all"){
            $accre = "%";
        }else{
            $accre = $request->accre;
        }
        

      return Datatables::of(DB::connection('delivery_inspection_db') ->table('tbl_rla_details')
            ->select("rlaId as id", "coop_name", "sg_id", "coopAccreditation", "moaNumber", "labNo",
                "lotNo", "certificationDate", "seedVariety", "noOfBags", DB::raw("IF(IF(NOW() > certificationDate, DATEDIFF(NOW(), certificationDate), 0) >=90, '#FF8000', '' ) as color"))
           
            ->where("coopAccreditation", "LIKE" , $accre)
            ->groupBy("coopAccreditation")
            ->groupBy("labNo")
            ->groupBy("lotNo")
            ->groupBy("seedVariety")
        )
                ->addColumn('coopAccreditation', function($row){
                        if($row->color == ""){
                            $checkifduplicate = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_rla_details")
                                ->where("coopAccreditation", $row->coopAccreditation)
                                ->where("labNo", $row->labNo)
                                ->where("lotNo", $row->lotNo)
                                ->where("seedVariety", $row->seedVariety)
                                ->get();
                            if(count($checkifduplicate)>1){
                                $row->color = "#088A29";
                            }
                        }



                        $checkifExceeds = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
                            ->where("is_cancelled", 0)
                            ->where("coopAccreditation", $row->coopAccreditation)
                            ->where("seedTag", $row->labNo.'/'.$row->lotNo)
                            ->sum("totalBagCount");

                        if($checkifExceeds > 0){
                                $row->color = "red";
                         }




                       return '<font style="color:'.$row->color.';">'.$row->coopAccreditation.'</font>';
                })
                ->addColumn('moaNumber', function($row){
                       return '<font style="color:'.$row->color.';">'.$row->moaNumber.'</font>';
                })
                ->addColumn('labNo', function($row){
                       return '<font style="color:'.$row->color.';">'.$row->labNo.'</font>';
                })
                ->addColumn('lotNo', function($row){
                       return '<font style="color:'.$row->color.';">'.$row->lotNo.'</font>';
                })
                ->addColumn('certificationDate', function($row){
                       return '<font style="color:'.$row->color.';">'.$row->certificationDate.'</font>';
                })
                ->addColumn('seedVariety', function($row){
                       return '<font style="color:'.$row->color.';">'.$row->seedVariety.'</font>';
                })
                ->addColumn('noOfBags', function($row){
                       return '<font style="color:'.$row->color.';">'.$row->noOfBags.'</font>';
                })
                ->addColumn('coop_name', function($row){
                        $coop_info = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")
                            ->where("accreditation_no", $row->coopAccreditation)    
                            ->first();
                    

                        if(count($coop_info)>0){
                            return '<a data-target="#info_modal" style="cursor: pointer;" data-toggle="modal" data-type_modal="coop" data-coop_accre="'.$coop_info->accreditation_no.'" data-coop="'.$coop_info->coopName.'" data-moa="'.$coop_info->current_moa.'" ><font style="color:'.$row->color.';">'.$coop_info->coopName.'</font></a>';
                        }else{
                            return "No Coop on Library (".$row->coopAccreditation.")";
                        }
                })
                ->addColumn('sg_id', function($row){
                        $sg_info = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_seed_grower")
                            ->select("tbl_seed_grower.full_name", "tbl_cooperatives.coopName", "tbl_seed_grower.coop_accred")
                            ->join($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives", "tbl_seed_grower.coop_accred", "=", "tbl_cooperatives.accreditation_no" )
                            ->where("sg_id", $row->sg_id)    
                            ->where("is_active", 1)
                            ->first();
                        if(count($sg_info)>0){
                            return '<a data-target="#info_modal" style="cursor: pointer;" data-toggle="modal" data-type_modal="sg" data-coop_accre="'.$sg_info->coop_accred.'" data-coop="'.$sg_info->coopName.'" data-full="'.$sg_info->full_name.'" ><font style="color:'.$row->color.';">'.$sg_info->full_name.' </font></a>';
                        }else{
                            return "No SG on Library (".$row->sg_id.")";
                        }
                })


                ->addColumn('action', function($row){
                        if(Auth::user()->userId == 28 || Auth::user()->userId == 370 || Auth::user()->userId == 2 || Auth::user()->userId == 2618 || Auth::user()->roles->first()->name == "system-admin"|| Auth::user()->roles->first()->name == "rcef-programmer"|| Auth::user()->roles->first()->name == "branch-it" || Auth::user()->userId == 504){


                        $checkifExceeds = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
                            ->where("is_cancelled", 0)
                            ->where("coopAccreditation", $row->coopAccreditation)
                            ->where("seedTag", $row->labNo.'/'.$row->lotNo)
                            ->sum("totalBagCount");


                        $checkActualDelivery = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
                            ->where("isRejected", 0)
                            ->where("seedTag", $row->labNo.'/'.$row->lotNo)
                            ->sum("totalBagCount");
                        // dd($checkActualDelivery);
                        if($checkActualDelivery > 0){
                            return '<a onclick="" class="btn btn-dark btn-md" disabled> <i class="fa fa-pencil-square-o" aria-hidden="true"></i>Edit</a>';
                        }
                        else if($checkifExceeds > 0){
                            return '<a onclick="window.open('."'".'https://rcef-seed.philrice.gov.ph/rcef_'.substr($GLOBALS['season_prefix'], 0, -1).'/cooperatives/rla/edit/'.$row->id.''."'".')" class="btn btn-warning btn-md" > <i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit</a>';
                        }
                        else{
                            return '<a onclick="window.open('."'".'https://rcef-seed.philrice.gov.ph/rcef_'.substr($GLOBALS['season_prefix'], 0, -1).'/cooperatives/rla/edit/'.$row->id.''."'".')" class="btn btn-warning btn-md" > <i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit</a> <a onclick="openDeleteModal('.$row->id.')" class="btn btn-danger btn-md"> <i class="fa fa-trash" aria-hidden="true"></i> Delete</a>';
                         }



                   
                        }
                })


//https://rcef-seed.philrice.gov.ph/rcef_ws2021/cooperatives/rla/edit/395
             ->make(true);




        




    }

    public function table_dataMissing(Request $request){
        if($request->accre == "all"){
            $accre = "%";
        }else{
            $accre = $request->accre;
        }
        $varieties = [];
        $getSeedVariety = DB::table($GLOBALS['season_prefix'].'seed_seed.seed_characteristics')
        ->selectRaw('DISTINCT(variety)')
        ->get('variety');
        
        foreach ($getSeedVariety as $variety)
        {
            array_push($varieties,$variety->variety);
        }
        // dd($varieties);

      return Datatables::of(DB::connection('delivery_inspection_db') ->table('tbl_rla_details')
            ->select("rlaId as id", "coop_name", "sg_id", "coopAccreditation", "moaNumber", "labNo",
                "lotNo", "certificationDate", "seedVariety", "noOfBags", DB::raw("IF(IF(NOW() > certificationDate, DATEDIFF(NOW(), certificationDate), 0) >=90, '#FF8000', '' ) as color"))
           
            ->where("coopAccreditation", "LIKE" , $accre)
            ->whereNotIn('seedVariety',$varieties)
            ->groupBy("coopAccreditation")
            ->groupBy("labNo")
            ->groupBy("lotNo")
            ->groupBy("seedVariety")
        )
                ->addColumn('coopAccreditation', function($row){
                        if($row->color == ""){
                            $checkifduplicate = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_rla_details")
                                ->where("coopAccreditation", $row->coopAccreditation)
                                ->where("labNo", $row->labNo)
                                ->where("lotNo", $row->lotNo)
                                ->where("seedVariety", $row->seedVariety)
                                ->get();
                            if(count($checkifduplicate)>1){
                                $row->color = "#088A29";
                            }
                        }



                        $checkifExceeds = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
                            ->where("is_cancelled", 0)
                            ->where("coopAccreditation", $row->coopAccreditation)
                            ->where("seedTag", $row->labNo.'/'.$row->lotNo)
                            ->sum("totalBagCount");

                        if($checkifExceeds >= $row->noOfBags){
                                $row->color = "red";
                         }




                       return '<font style="color:'.$row->color.';">'.$row->coopAccreditation.'</font>';
                })
                ->addColumn('moaNumber', function($row){
                       return '<font style="color:'.$row->color.';">'.$row->moaNumber.'</font>';
                })
                ->addColumn('labNo', function($row){
                       return '<font style="color:'.$row->color.';">'.$row->labNo.'</font>';
                })
                ->addColumn('lotNo', function($row){
                       return '<font style="color:'.$row->color.';">'.$row->lotNo.'</font>';
                })
                ->addColumn('certificationDate', function($row){
                       return '<font style="color:'.$row->color.';">'.$row->certificationDate.'</font>';
                })
                ->addColumn('seedVariety', function($row){
                       return '<font style="color:'.$row->color.';">'.$row->seedVariety.'</font>';
                })
                ->addColumn('noOfBags', function($row){
                       return '<font style="color:'.$row->color.';">'.$row->noOfBags.'</font>';
                })
                ->addColumn('coop_name', function($row){
                        $coop_info = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")
                            ->where("accreditation_no", $row->coopAccreditation)    
                            ->first();
                    

                        if(count($coop_info)>0){
                            return '<a data-target="#info_modal" style="cursor: pointer;" data-toggle="modal" data-type_modal="coop" data-coop_accre="'.$coop_info->accreditation_no.'" data-coop="'.$coop_info->coopName.'" data-moa="'.$coop_info->current_moa.'" ><font style="color:'.$row->color.';">'.$coop_info->coopName.'</font></a>';
                        }else{
                            return "No Coop on Library (".$row->coopAccreditation.")";
                        }
                })
                ->addColumn('sg_id', function($row){
                        $sg_info = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_seed_grower")
                            ->select("tbl_seed_grower.full_name", "tbl_cooperatives.coopName", "tbl_seed_grower.coop_accred")
                            ->join($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives", "tbl_seed_grower.coop_accred", "=", "tbl_cooperatives.accreditation_no" )
                            ->where("sg_id", $row->sg_id)    
                            ->where("is_active", 1)
                            ->first();
                        if(count($sg_info)>0){
                            return '<a data-target="#info_modal" style="cursor: pointer;" data-toggle="modal" data-type_modal="sg" data-coop_accre="'.$sg_info->coop_accred.'" data-coop="'.$sg_info->coopName.'" data-full="'.$sg_info->full_name.'" ><font style="color:'.$row->color.';">'.$sg_info->full_name.' </font></a>';
                        }else{
                            return "No SG on Library (".$row->sg_id.")";
                        }
                })


                ->addColumn('action', function($row){
                        if(Auth::user()->userId == 28 || Auth::user()->userId == 370 || Auth::user()->userId == 2 || Auth::user()->userId == 2618 || Auth::user()->roles->first()->name == "system-admin"|| Auth::user()->roles->first()->name == "rcef-programmer"|| Auth::user()->userId == 504){


                        $checkifExceeds = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
                            ->where("is_cancelled", 0)
                            ->where("coopAccreditation", $row->coopAccreditation)
                            ->where("seedTag", $row->labNo.'/'.$row->lotNo)
                            ->sum("totalBagCount");

                        if($checkifExceeds >= $row->noOfBags){
                                  return '<a onclick="" class="btn btn-dark btn-md" disabled> <i class="fa fa-pencil-square-o" aria-hidden="true"></i>Edit</a>';
                         }else{
                                  return '<a onclick="window.open('."'".'https://rcef-seed.philrice.gov.ph/rcef_'.substr($GLOBALS['season_prefix'], 0, -1).'/cooperatives/rla/edit/'.$row->id.''."'".')" class="btn btn-warning btn-md" > <i class="fa fa-pencil-square-o" aria-hidden="true"></i>Edit</a>';
                         }



                   
                        }
                })


//https://rcef-seed.philrice.gov.ph/rcef_ws2021/cooperatives/rla/edit/395
             ->make(true);

    }





    public function table_data_old(Request $request){

        if($request->accre == "all"){
            $accre = "%";
        }else{
            $accre = $request->accre;
        }

        $rla_list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_rla_details")
            ->where("coopAccreditation", "LIKE" , $accre)
            ->groupBy("coopAccreditation")
            ->groupBy("labNo")
            ->groupBy("lotNo")
            ->groupBy("seedVariety")
            ->get();

        $tbl_list = array();
            foreach ($rla_list as $key => $rla_info) {
                $color = "";
                $checkifduplicate = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_rla_details")
                    ->where("coopAccreditation", "LIKE" , $rla_info->coopAccreditation)
                    ->where("labNo", $rla_info->labNo)
                    ->where("lotNo", $rla_info->lotNo)
                    ->where("seedVariety", $rla_info->seedVariety)
                    ->get();
                if(count($checkifduplicate)>1){
                    $color = "#088A29";
                }

                $date_now = date("Y-m-d");
                $date_to = date("Y-m-d", strtotime($rla_info->certificationDate));
            

                if(strtotime($date_to) < strtotime($date_now) ){
                     $days = abs(strtotime($date_to) - strtotime($date_now));
                     $days = $days / 86400;
                      if($days >= 90){
                            $color = "#FF8000"; 
                      }
                }



                array_push($tbl_list, array(
                    "id" => $rla_info->rlaId,
                    "seed_coop" => $rla_info->coop_name,
                    "seed_grower" => $rla_info->sg_id,
                    "accre" => $rla_info->coopAccreditation,
                    "moa" => $rla_info->moaNumber,
                    "labNo" => $rla_info->labNo,
                    "lotNo" => $rla_info->lotNo,
                    "certificationDate" => $rla_info->certificationDate,
                    "seed_variety" => $rla_info->seedVariety,
                    "volume" => $rla_info->noOfBags,
                    "color" => $color    
                ));
            }


            $tbl_data = collect($tbl_list);

            return Datatables::of($tbl_data)
                ->addColumn('accre', function($row){
                       return '<font style="color:'.$row['color'].';">'.$row['accre'].'</font>';
                })
                ->addColumn('moa', function($row){
                       return '<font style="color:'.$row['color'].';">'.$row['moa'].'</font>';
                })
                ->addColumn('labNo', function($row){
                       return '<font style="color:'.$row['color'].';">'.$row['labNo'].'</font>';
                })
                ->addColumn('lotNo', function($row){
                       return '<font style="color:'.$row['color'].';">'.$row['lotNo'].'</font>';
                })
                ->addColumn('certificationDate', function($row){
                       return '<font style="color:'.$row['color'].';">'.$row['certificationDate'].'</font>';
                })
                ->addColumn('seed_variety', function($row){
                       return '<font style="color:'.$row['color'].';">'.$row['seed_variety'].'</font>';
                })
                ->addColumn('volume', function($row){
                       return '<font style="color:'.$row['color'].';">'.$row['volume'].'</font>';
                })
                ->addColumn('seed_coop', function($row){
                        $coop_info = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")
                            ->where("accreditation_no", $row["accre"])    
                            ->first();
                    

                        if(count($coop_info)>0){
                            return '<a data-target="#info_modal" style="cursor: pointer;" data-toggle="modal" data-type_modal="coop" data-coop_accre="'.$coop_info->accreditation_no.'" data-coop="'.$coop_info->coopName.'" data-moa="'.$coop_info->current_moa.'" ><font style="color:'.$row['color'].';">'.$coop_info->coopName.'</font></a>';
                        }else{
                            return "No Coop on Library";
                        }
                })
                ->addColumn('seed_grower', function($row){
                        $sg_info = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_seed_grower")
                            ->select("tbl_seed_grower.full_name", "tbl_cooperatives.coopName", "tbl_seed_grower.coop_accred")
                            ->join($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives", "tbl_seed_grower.coop_accred", "=", "tbl_cooperatives.accreditation_no" )
                            ->where("sg_id", $row["seed_grower"])    
                            ->where("is_active", 1)
                            ->first();
                        if(count($sg_info)>0){
                            return '<a data-target="#info_modal" style="cursor: pointer;" data-toggle="modal" data-type_modal="sg" data-coop_accre="'.$sg_info->coop_accred.'" data-coop="'.$sg_info->coopName.'" data-full="'.$sg_info->full_name.'" ><font style="color:'.$row['color'].';">'.$sg_info->full_name.' </font></a>';
                        }else{
                            return "No SG on Library";
                        }
                })


                ->addColumn('action', function($row){
                        if(Auth::user()->userId == 28 || Auth::user()->userId == 370 || Auth::user()->userId == 2 || Auth::user()->userId == 2618 || Auth::user()->roles->first()->name == "system-admin"){
                         return '<a onclick="window.open('."'".'https://rcef-seed.philrice.gov.ph/rcef_ws2022/cooperatives/rla/edit/'.$row["id"].''."'".')" class="btn btn-warning btn-md" > <i class="fa fa-pencil-square-o" aria-hidden="true"></i>Edit</a>';
                        }
                })


//https://rcef-seed.philrice.gov.ph/rcef_ws2021/cooperatives/rla/edit/395
             ->make(true);
    }


      public function graphData(Request $request){
        if($request->accre == "all"){
            $accre = "%";
        }else{
            $accre = $request->accre;
        }

        $bar_arr = array();
        $coop_list = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")
                ->where("accreditation_no", "like", $accre)
                ->orderBy("coopName", "ASC")
                ->get();
            foreach ($coop_list as $coop_info) {
                    $y = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_rla_details")
                        ->where("coopAccreditation", $coop_info->accreditation_no)
                        ->sum("noOfBags");

                array_push($bar_arr,array(
                    "name" =>  $coop_info->coopName,
                    "y" => intval($y),
                    "sliced" => true,
                ));
            }

            return $bar_arr;

    }




}




