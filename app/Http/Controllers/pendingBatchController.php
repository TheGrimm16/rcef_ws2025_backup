<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Input;
use Illuminate\Filesystem\Filesystem;

use Illuminate\Support\Facades\Schema;
use Config;
use DB;
use Excel;

use App\HistoryMonitoring;
use App\Regions;
use App\Provinces;
use App\Municipalities;

use Session;
use Auth;

class pendingBatchController extends Controller
{
      public function index()
    {
         $provinces_list = DB::connection('delivery_inspection_db')->table('tbl_delivery_transaction as m')
            ->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point as p', 'm.prv_dropoff_id', '=', 'p.prv_dropoff_id')
            ->select('m.id as id', 'm.batchTicketNumber as batch_ticket', 'm.instructed_delivery_volume as volume', 'm.moa_number as coop', 'm.delivery_date as delivery', 'p.prv_dropoff_id as dropoff','p.province as province')
            ->where('m.status', 0)
            ->orderBy('p.province', 'ASC')
            ->groupBy('p.province')
            ->get();   //FOR PSTOCS
			/*
		$provinces_list = DB::connection('rcep_transfers_db')->table('transfer_logs')
            ->select('*')
            ->where('seed_variety','like','ALL_SEEDS_TRANSFER')
            ->orderBy('destination_province', 'ASC')
            ->groupBy('destination_province')
            ->get();	*/
			
        return view('pendingBatch.home')
             ->with('municipal_list', $provinces_list)
             ->with('user_level', Auth::user()->roles->first()->name)
             ->with('userName', Auth::user()->username);
    }

	 

     public function get_municipalities($province) //PSTOCS
    {
         $municipalities_list = DB::connection('delivery_inspection_db')->table('tbl_delivery_transaction as m')
            ->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point as p', 'm.prv_dropoff_id', '=', 'p.prv_dropoff_id')
            ->select('m.id as id','m.batchTicketNumber as batch_ticket', 'm.instructed_delivery_volume as volume', 'm.moa_number as coop', 'm.delivery_date as delivery', 'p.prv_dropoff_id as dropoff','p.province as province','p.municipality as municipality')
            ->where('m.status', 0)
            ->where('p.province',$province)
            ->orderBy('municipality', 'ASC')
            ->groupBy('municipality')
            ->get();   
        echo json_encode($municipalities_list); 
    }

     public function get_dop($province,$municipality) //PSTOCS
    {
         $dropOffPoints = DB::connection('delivery_inspection_db')->table('tbl_delivery_transaction as m')
            ->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point as p', 'm.prv_dropoff_id', '=', 'p.prv_dropoff_id')
            ->select('m.id as id', 'm.batchTicketNumber as batch_ticket', 'm.instructed_delivery_volume as volume', 'm.moa_number as coop', 'm.delivery_date as delivery', 'p.prv_dropoff_id as dropoff','p.province as province','p.municipality as municipality','p.dropOffPoint as dropOffPoint')
            ->where('m.status', 0)
            ->where('p.province',$province)
            ->where('p.municipality',$municipality)
            ->orderBy('p.dropOffPoint', 'ASC')
            ->groupBy('p.dropOffPoint')
            ->get();   
        echo json_encode($dropOffPoints); 
    }




    public function generatePendingData(Request $request){ //FOR PROVINCE ONLY
    	
            return Datatables::of(DB::connection('delivery_inspection_db')->table('tbl_delivery_transaction as m')
            ->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point as p', 'm.prv_dropoff_id', '=', 'p.prv_dropoff_id')
                ->leftjoin($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as c', 'm.moa_number','=','c.current_moa')            

            ->select('m.id as id', 'm.batchTicketNumber as batch_ticket', 'm.instructed_delivery_volume as volume', 'c.coopName as coop', 'm.delivery_date as delivery',  DB::raw("CONCAT(p.province,', ',p.municipality,'->',p.dropOffPoint) AS dropoff"),'p.province as province','p.municipality as municipality','m.user_id as user')
            ->where('status', 0)
            ->where('p.province',$request->provName)
            ->orderBy('batch_ticket', 'ASC')
            ->groupBy('id')
            )
            ->addColumn('action', function($row){
				if (Auth::user()->roles->first()->name == "system-admin"){
                return "  <button class='btn btn-warning btn-xs' onclick='cancelBatch(this.value);' name='cancel".$row->id."' id='cancel' value='".$row->id."''>Cancel</button>";
                }
				})
                ->make(true);
                       
    } //QUERY DATA TABLE

    public function generatePendingData2(Request $request){ //FOR PROVINCE AND MUNICIPALITY ONLY
        

            if($request->muniName == "--ALL MUNICIPALITY--"){
                $muni = '%';
            }else{
                $muni = '%'.$request->muniName.'%';
            }

            return Datatables::of(DB::connection('delivery_inspection_db')->table('tbl_delivery_transaction as m')
            ->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point as p', 'm.prv_dropoff_id', '=', 'p.prv_dropoff_id')
                ->leftjoin($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as c', 'm.moa_number','=','c.current_moa')            

            ->select('m.id as id', 'm.batchTicketNumber as batch_ticket', 'm.instructed_delivery_volume as volume', 'c.coopName as coop', 'm.delivery_date as delivery',  DB::raw("CONCAT(p.province,', ',p.municipality,'->',p.dropOffPoint) AS dropoff"),'p.province as province','p.municipality as municipality','m.user_id as user')
            ->where('status', 0)
            ->where('p.province',$request->provName)
            ->where('p.municipality', 'like', $muni)
            ->orderBy('batch_ticket', 'ASC')
            ->groupBy('id')
            )
            ->addColumn('action', function($row){
				if (Auth::user()->roles->first()->name == "system-admin"){
             return "  <button class='btn btn-warning btn-xs' onclick='cancelBatch(this.value);' name='cancel".$row->id."' id='cancel' value='".$row->id."''>Cancel</button>";
                }
				})
                ->make(true);
                       
    } //QUERY DATA TABLE




 public function generatePendingData3(Request $request){ //FOR PROVINCE AND MUNICIPALITY ONLY
        
       if($request->dropOffName == "--All Drop Off Point--"){
                $dop = '%';
            }else{
                $dop = '%'.$request->dropOffName.'%';
            }
        if($request->muniName == "--ALL MUNICIPALITY--"){
                $muni = '%';
            }else{
                $muni = '%'.$request->muniName.'%';
            }


            return Datatables::of(DB::connection('delivery_inspection_db')->table('tbl_delivery_transaction as m')
            ->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point as p', 'm.prv_dropoff_id', '=', 'p.prv_dropoff_id')
                ->leftjoin($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as c', 'm.moa_number','=','c.current_moa')            

            ->select('m.id as id', 'm.batchTicketNumber as batch_ticket', 'm.instructed_delivery_volume as volume', 'c.coopName as coop', 'm.delivery_date as delivery',  DB::raw("CONCAT(p.province,', ',p.municipality,'->',p.dropOffPoint) AS dropoff"),'p.province as province','p.municipality as municipality','m.user_id as user')
            ->where('status', 0)
            ->where('p.province',$request->provName)
            ->where('p.municipality', 'like', $muni)
            ->where('p.dropOffPoint','like', $dop)
            ->orderBy('batch_ticket', 'ASC')
            ->groupBy('id')
            )

            ->addColumn('action', function($row){
				if (Auth::user()->roles->first()->name == "system-admin"){
             return "  <button class='btn btn-warning btn-xs' onclick='cancelBatch(this.value);' name='cancel".$row->id."' id='cancel' value='".$row->id."''>Cancel</button>";
                }
				})
                ->make(true);
        


            
                       
    } //QUERY DATA TABLE




     public function export_transfer_history($province, $municipality, $dopoint){
        ini_set('memory_limit', '-1');
        DB::beginTransaction();
        try {

            if($dopoint == "--All Drop Off Point--"){
                $dop = '%';
            }else{
                $dop = '%'.$dopoint.'%';
            }
        if($municipality == "--ALL MUNICIPALITY--"){
                $muni = '%';
            }else{
                $muni = '%'.$municipality.'%';
            }


            $data = DB::connection('delivery_inspection_db')->table('tbl_delivery_transaction as m')
            ->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point as p', 'm.prv_dropoff_id', '=', 'p.prv_dropoff_id')
                ->leftjoin($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as c', 'm.moa_number','=','c.current_moa')            

            ->select('m.id as id', 'm.batchTicketNumber as batch_ticket',  'c.coopName as coop', 'm.instructed_delivery_volume as volume', DB::raw("CONCAT(p.province,', ',p.municipality,'->',p.dropOffPoint) AS dropoff"), 'm.delivery_date as delivery')
            ->where('status', 0)
            ->where('p.province',$province)
            ->where('p.municipality', 'like', $muni)
            ->where('p.dropOffPoint','like', $dop)
            ->orderBy('batch_ticket', 'ASC')
            ->groupBy('id')
            ->get();
            
            //CHANGE actual_delivery_ds and dropoffpoint when transfer on live

               

            $excel_data = json_decode(json_encode($data), true); //convert collection to associative array to be converted to excel
             
      

            return Excel::create($province."_".$municipality."_".$dopoint."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
                $excel->sheet("Pending Delivery list", function($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data, null, 'A1', false, false);
                    $sheet->prependRow(1, array(
                        '#', 'Batch #', 'Cooperative', "Bag Count", "Drop off Point ", 
                         "Delivery Date"
                    ));
                    $sheet->freezeFirstRow();
                    $sheet->getColumnDimension('A')->setVisible(false);
                });
            })->download('xlsx');

            DB::commit();
        
        } catch (\Exception $e) {
            DB::rollback();
            return $e;
        }
    }




	//TEMP
	public function export_farmer_yield_zero($prv_num){
        ini_set('memory_limit', '-1');
        DB::beginTransaction();
        try {

           

        $data = DB::table($prv_num.".farmer_profile")
        ->select('*')
        ->where('yield', '!=', '')
        ->orderBy('lastName', 'asc')
        ->orderBy('firstName', 'asc')
        ->get();
//CHANGE actual_delivery_ds and dropoffpoint when transfer on live

               

            $excel_data = json_decode(json_encode($data), true); //convert collection to associative array to be converted to excel
            $prv_num2 = substr($prv_num, 4);
			 
			 
			 $get_provName = DB::table($GLOBALS['season_prefix']."sdms_db_dev.lib_provinces")
			 ->select('provDesc')
			 ->where('provCode', $prv_num2)
			 ->first();
			
			$provName = $get_provName->provDesc;
	  
	  
	  
	  
	  
	  

            return Excel::create($provName."_".date("Y-m-d g:i A"), function($excel) use ($excel_data,$provName) {
                $excel->sheet($provName, function($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data, null, 'A1', false, false);
                    $sheet->prependRow(1, array(
                        '#', 'farmer Id', 'distribution id', "last name", "first name ", 
                         "middle name", "extension name", "full name", "gender", "birthdate", "region", "province", "municipality", "barangay", "affillationType", "affillationName", "affillationAccreditation", "isDaAccridited", "isLGU", "rsbsa_control_no", "isNew", "send", "update","actual_area", "season", "yield", "area" 
                    ));
                    $sheet->freezeFirstRow();
                    $sheet->getColumnDimension('A')->setVisible(false);
                });
            })->download('xlsx');

            DB::commit();
        
        } catch (\Exception $e) {
            DB::rollback();
            return $e;
        }
    }
	
	




}
