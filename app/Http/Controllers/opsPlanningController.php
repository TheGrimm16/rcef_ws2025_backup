<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Regions;
use App\Provinces;
use App\Municipalities;
use App\FarGeneration;
use Response;
use Auth;
use DB;
use PDFTIM;
use DOMPDF;
use Excel;
use Illuminate\Filesystem\Filesystem;
use PHPExcel;   
use PHPExcel_IOFactory;
use Yajra\Datatables\Datatables;


class opsPlanningController extends Controller
{
    public function index(){
        $regionList = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->orderBy("region_sort")
            ->groupBy("regionName")
            ->get();

        if(Auth::user()->userId == 28 || Auth::user()->userId == 370 || Auth::user()->userId == 2 || Auth::user()->username == "r.benedicto" || Auth::user()->username == "racariaga" || Auth::user()->username == "19-0922" || Auth::user()->username == "kjgdeleon"){
            return view("opsPlanning.index")
            ->with("regionList", $regionList);
        }else{
              $mss = "No Access Privilege";
         return view('utility.pageClosed',compact("mss"));
        }




        
    }

    public function provinceList(Request $request){
        $provinces = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->where("regionName", "like", $request->region)
            ->groupBy("province")
            ->orderBy("region_sort", "ASC")
            ->get();
        return json_encode($provinces);
    }

     public function municipalityList(Request $request){
        $provinces = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->where("regionName", "like", $request->region)
            ->where("province", "like", $request->province)
            ->groupBy("municipality")
            ->orderBy("region_sort", "ASC")
            ->get();
        return json_encode($provinces);
    }

       public function dopList(Request $request){
        $provinces = DB::table($GLOBALS['season_prefix']."rcep_ops_del_sched.tbl_schedule")
            ->where("region", "like", $request->region)
            ->where("province", "like", $request->province)
            ->where("municipality", "like", $request->municipality)
            
            ->groupBy("dropOffPoint")
            ->orderBy("dropOffPoint", "ASC")
            ->get();
        return json_encode($provinces);
    }


     public function modal_data(Request $request){
       

        $coop_list = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")
                ->groupBy("accreditation_no")
                ->get();

        $inspector_list = DB::table("role_user")
                ->select("users.*")
                ->join("users", "users.userId", "=", "role_user.userId")
                ->where("roleId", 8)
                ->groupBy("users.username")
                ->get();
                
        $pc_list = DB::table("role_user")
                ->select("users.*")
                ->join("users", "users.userId", "=", "role_user.userId")
                ->where("roleId", 1)
                ->groupBy("users.username")
                ->get();
        
        $dop_list = DB::table($GLOBALS['season_prefix']."rcep_ops_del_sched.tbl_schedule")
                ->where("region", $request->region)
                ->where("province", $request->province)
                ->where("municipality", $request->municipality)
                ->groupBy("dropOffPoint")
                ->get();




        $modal_data = array(
            "coop_list" => $coop_list,
            "inspector_list" => $inspector_list,
            "pc_list" => $pc_list,
            "dop_list" => $dop_list,
            );
         return $modal_data;
    }

    public function addSchedule(Request $request){
            $prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                ->where("regionName", $request->region)
                ->where("province", $request->province)
                ->where("municipality", $request->municipality)
                ->first();

            if(count($prv)>0){
                $prv = $prv->prv;
            }else{
                $prv = 0;
            }


            $coopName = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")
                ->where("coopId", $request->coop_id)
                ->first();
            if(count($coopName)>0){
                $coopName = $coopName->coopName;
            }else{
                $coopName = "-";
            }


        //INSERT DATA
      $sched =  DB::table($GLOBALS['season_prefix']."rcep_ops_del_sched.tbl_schedule")
            ->insertGetId([
                "region" => $request->region,
                "province" => $request->province,
                "municipality" => $request->municipality,
                "dropOffPoint" => $request->dropoff_point,
                "bags" => $request->bags,
                "delivery_date" => $request->delivery_date,
                "distribution_date" => $request->distribution_date,
                "inspector" => $request->inspector,
                "assigned_pc" => $request->pc,
                "status" => $request->status,
                "remarks" => $request->remarks,
                "coop_id" => $request->coop_id,
                "prv_id" => $prv,
                "seed_coop" => $coopName
            ]);




            DB::table($GLOBALS['season_prefix']."rcep_ops_del_sched.tbl_logs")
                ->insert([
                    "action" => "add",
                    "id" => $sched,
                    "username" => Auth::user()->username,
                ]);

        return json_encode("Successful Submission of Schedule");
    }


    public function updateSchedule(Request $request){
            $prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                ->where("regionName", $request->region)
                ->where("province", $request->province)
                ->where("municipality", $request->municipality)
                ->first();

            if(count($prv)>0){
                $prv = $prv->prv;
            }else{
                $prv = 0;
            }


            $coopName = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")
                ->where("coopId", $request->coop_id)
                ->first();
            if(count($coopName)>0){
                $coopName = $coopName->coopName;
            }else{
                $coopName = "-";
            }


        //INSERT DATA
        DB::table($GLOBALS['season_prefix']."rcep_ops_del_sched.tbl_schedule")
            ->where("id", $request->id)
            ->update([
                "region" => $request->region,
                "province" => $request->province,
                "municipality" => $request->municipality,
                "dropOffPoint" => $request->dropoff_point,
                "bags" => $request->bags,
                "delivery_date" => $request->delivery_date,
                "distribution_date" => $request->distribution_date,
                "inspector" => $request->inspector,
                "assigned_pc" => $request->pc,
                "status" => $request->status,
                "remarks" => $request->remarks,
                "coop_id" => $request->coop_id,
                "prv_id" => $prv,
                "seed_coop" => $coopName
            ]);


            DB::table($GLOBALS['season_prefix']."rcep_ops_del_sched.tbl_logs")
                ->insert([
                    "action" => "update",
                    "id" => $request->id,
                    "username" => Auth::user()->username,
                ]);


        return json_encode("Successful Updating of Schedule");
    }


    public function deleteThis(Request $request){
         DB::table($GLOBALS['season_prefix']."rcep_ops_del_sched.tbl_schedule")
            ->where("id", $request->id)
            ->delete();
        DB::table($GLOBALS['season_prefix']."rcep_ops_del_sched.tbl_logs")
                ->insert([
                    "action" => "delete",
                    "id" => $request->id,
                    "username" => Auth::user()->username,
                ]);
        return json_encode("Successful Deleting of Schedule");
    }





    public function load_table(Request $request){
        if($request->region == "all")$region="%";else{$region=$request->region;}
        if($request->province == "all" || $request->province == "0")$province="%";else{$province=$request->province;}
        if($request->municipality == "all" || $request->municipality == "0")$municipality="%";else{$municipality=$request->municipality;}
        if($request->dop == "all" || $request->dop == "0")$dop="%";else{$dop=$request->dop;}
        if($request->status == "all")$status="%";else{$status=$request->status;}



        return Datatables::of(DB::table($GLOBALS['season_prefix'].'rcep_ops_del_sched.tbl_schedule')
                ->where('region', 'like', $region)
                ->where('province', 'like', $province)
                ->where('municipality', 'like', $municipality)
                ->where('dropOffPoint', 'like', $dop)
                ->where('status', 'like', $status)     
            )
            ->addColumn('action', function($row){
                if($row->delivery_date != '0000-00-00'){
                  $del =  date('Y-m-d',strtotime($row->delivery_date));
                }else{
                    $del ="";
                }

                if($row->distribution_date != '0000-00-00'){
                  $dis =  date('Y-m-d',strtotime($row->distribution_date));
                }else{
                    $dis ="";
                }                
                
                $btn = '<a href="#" style="float:right; width:80px; height:30px;"  data-toggle="modal" data-target="#add_edit_modal" data-eventtarget="edit"
                    data-id="'.$row->id.'"
                    data-region="'.$row->region.'"
                    data-province="'.$row->province.'"
                    data-municipality="'.$row->municipality.'"
                    data-dropoffpoint="'.$row->dropOffPoint.'"
                    data-bags="'.$row->bags.'"
                    data-status="'.$row->status.'"
                    data-delivery="'.$del.'"
                    data-distribution="'.$dis.'"
                    data-inspector="'.$row->inspector.'"
                    data-pc="'.$row->assigned_pc.'"
                    data-remarks="'.$row->remarks.'"
                    data-coop_id="'.$row->coop_id.'"
                    
                 class="btn btn-success btn-sm"> <i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit </a>';
              
                 $btn .= '<a href="#" style="float:right; width:80px; height:30px;" onclick="deleteThis('.$row->id.')"
                    
                 class="btn btn-danger btn-sm"> <i class="fa fa-trash" aria-hidden="true"></i> Delete </a>';

                 return $btn;

                })


            ->make(true);

    }

    public function exportToExcel($status,$region,$province,$municipality,$dop){    

        if($status == "all")$status = "%";
        if($region == "all")$region = "%";
        if($province == "all" || $province == "0")$province= "%";
        if($municipality == "all" || $municipality == "0")$municipality= "%";
        if($dop == "all" || $dop == "0")$dop= "%";        

        $list = DB::table($GLOBALS['season_prefix'].'rcep_ops_del_sched.tbl_schedule')
                ->select("region", "province", "municipality",DB::raw("DATE_FORMAT(delivery_date, '%M') as del_m"),DB::raw("DATE_FORMAT(delivery_date, '%d') as del_d"),DB::raw("DATE_FORMAT(delivery_date, '%Y') as del_y"), DB::raw("DATE_FORMAT(distribution_date, '%M') as dis_m"),DB::raw("DATE_FORMAT(distribution_date, '%d') as dis_d"),DB::raw("DATE_FORMAT(distribution_date, '%Y') as dis_y"), "dropOffPoint", "seed_coop", "bags", "inspector", "assigned_pc", "remarks", "status")      
                ->where('region', 'like', $region)
                ->where('province', 'like', $province)
                ->where('municipality', 'like', $municipality)
                ->where('dropOffPoint', 'like', $dop)
                ->where('status', 'like', $status)     
                ->get();
      
        $excel_data = json_decode(json_encode($list), true); //convert collection to associative array to be converted to excel
            return Excel::create("RCEF_NATIONAL_DISTRIBUTION_SCHEDULE"."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
                $excel->sheet("SCHEDULE_LIST", function($sheet) use ($excel_data) {
        $row = 2;
        $col = "A";
                    $sheet->prependRow($row, array("RCEF National Delivery and Distribution Schedule"));
                    $sheet->mergeCells("A2:P2");
                    $sheet->cells("A2:P2", function ($cells){
                                    $cells->setAlignment('left');
                                    $cells->setFontWeight('bold');
                                    $cells->setFontSize(16);
                                }); 
                //HEADER
                $sheet->mergeCells($col."4:".$col."5");
                $sheet->cell($col."4", function($cells){ $cells->setValue("Region");});
                $col++;
                $sheet->mergeCells($col."4:".$col."5");
                $sheet->cell($col."4", function($cells){ $cells->setValue("Province");});
                $col++;
                $sheet->mergeCells($col."4:".$col."5");
                $sheet->cell($col."4", function($cells){ $cells->setValue("Municipality to be Served");});
                $col++;
                $col2 = $col;
                $sheet->cell($col2."5", function($cells){ $cells->setValue("Month");});
                $col2++;
                $sheet->cell($col2."5", function($cells){ $cells->setValue("Day");});
                $col2++;
                $sheet->cell($col2."5", function($cells){ $cells->setValue("Year");});
                $sheet->mergeCells($col."4:".$col2."4");
                $sheet->cell($col."4", function($cells){ $cells->setValue("Date of Delivery");});
                $col = $col2;
                $col++;
                $col2 = $col;
                    $sheet->cell($col2."5", function($cells){ $cells->setValue("Month");});
                $col2++;
                    $sheet->cell($col2."5", function($cells){ $cells->setValue("Day");});
                $col2++;
                     $sheet->cell($col2."5", function($cells){ $cells->setValue("Year");});
                $sheet->mergeCells($col."4:".$col2."4");
                $sheet->cell($col."4", function($cells){ $cells->setValue("Distribution Date");});
                $col = $col2;
                $col++;

                $sheet->mergeCells($col."4:".$col."5");
                $sheet->cell($col."4", function($cells){ $cells->setValue("Drop-off Point");});
                $col++;
                $sheet->mergeCells($col."4:".$col."5");
                $sheet->cell($col."4", function($cells){ $cells->setValue("Seed Cooperative to Delivery");});
                $col++;
                $sheet->mergeCells($col."4:".$col."5");
                $sheet->cell($col."4", function($cells){ $cells->setValue("Total No. of Bags to be");});
                $col++;
                $sheet->mergeCells($col."4:".$col."5");
                $sheet->cell($col."4", function($cells){ $cells->setValue("Delivery Inspectors");});
                $col++;
                $sheet->mergeCells($col."4:".$col."5");
                $sheet->cell($col."4", function($cells){ $cells->setValue("Assigned PC");});
                $col++;
                $sheet->mergeCells($col."4:".$col."5");
                $sheet->cell($col."4", function($cells){ $cells->setValue("Remarks");});
                $col++;
                $sheet->mergeCells($col."4:".$col."5");
                $sheet->cell($col."4", function($cells){ $cells->setValue("Status");});
                
                $sheet->cells("A4:P5", function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                    $cells->setBackground('#00bd00');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 



                 


$row = 6;
                foreach ($excel_data as $key => $value) {
                    
                    $col = "A";
                    
                    $sheet->cell($col.$row, function($cells) use ($value){ $cells->setValue($value["region"]);});
                    $col++;
                    $sheet->cell($col.$row, function($cells)  use ($value){ $cells->setValue($value["province"]);});
                    $col++;             
                    $sheet->cell($col.$row, function($cells)  use ($value){ $cells->setValue($value["municipality"]);});
                    $col++;
                    $sheet->cell($col.$row, function($cells)  use ($value){ $cells->setValue($value["del_m"]);});
                    $col++;
                    $sheet->cell($col.$row, function($cells)  use ($value){ $cells->setValue($value["del_d"]);});
                    $col++;
                    $sheet->cell($col.$row, function($cells)  use ($value){ $cells->setValue($value["del_y"]);});
                    $col++;
                    $sheet->cell($col.$row, function($cells)  use ($value){ $cells->setValue($value["dis_m"]);});
                    $col++;
                    $sheet->cell($col.$row, function($cells)  use ($value){ $cells->setValue($value["dis_d"]);});
                    $col++;
                    $sheet->cell($col.$row, function($cells)  use ($value){ $cells->setValue($value["dis_y"]);});
                    $col++;
                    $sheet->cell($col.$row, function($cells)  use ($value){ $cells->setValue($value["dropOffPoint"]);});
                    $col++;
                    $sheet->cell($col.$row, function($cells)  use ($value){ $cells->setValue($value["seed_coop"]);});
                    $col++;
                    $sheet->cell($col.$row, function($cells)  use ($value){ $cells->setValue($value["bags"]);});
                    $col++;
                    $sheet->cell($col.$row, function($cells)  use ($value){
                        $name = DB::table($GLOBALS['season_prefix']."sdms_db_dev.users")
                                    ->where("username", $value["inspector"])
                                    ->first();
                        if(count($name)>0){$name = $name->firstName." ".$name->middleName." ".$name->lastName; }else{$name = " - ";}
                         $cells->setValue(strtoupper($name));
                    });
                    $col++;
                    $sheet->cell($col.$row, function($cells)  use ($value){
                        $name = DB::table($GLOBALS['season_prefix']."sdms_db_dev.users")
                                    ->where("username", $value["assigned_pc"])
                                    ->first();
                            if(count($name)>0){$name = $name->firstName." ".$name->middleName." ".$name->lastName; }else{$name = " - ";}
                        $cells->setValue(strtoupper($name));
                    });
                    $col++;
                    $sheet->cell($col.$row, function($cells)  use ($value){ $cells->setValue($value["remarks"]);});
                    $col++;
                    $sheet->cell($col.$row, function($cells)  use ($value){ $cells->setValue($value["status"]);});
                    $col++;
                    $row++;
                }

                 $sheet->setWidth(array(
                    'A'     => 25,
                    'B'     => 25,
                    'C'     => 25,
                    'J'     => 45,
                    'K'     => 45,
                    'L'     => 25,
                    'M'     => 35,
                    'N'     => 35,
                    'O'     => 25,
                    'P'     => 25,
                ));

                }); 
            })->download('xlsx');
     


    }





}

